<?php

namespace SilverCart\Dev\Tasks;

use SilverCart\Model\Customer\DeletedCustomer;
use SilverCart\Model\Customer\DeletedCustomerReason;
use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use SilverStripe\Security\Member;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class DeleteCustomersTask extends BuildTask
{
    // SS6: segment als config (nicht zwingend, aber üblich)
    private static string $segment = 'sc-delete-customers';

    protected string $title = 'Delete customer accounts';
    protected static string $description = 'Deletes customer accounts marked for deletion';

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        // Wenn du bisher eigene printInfo() Helpers hast, kannst du sie weiter nutzen.
        // Sonst: $output->writeln('...');

        $this->process();

        return Command::SUCCESS;
    }

    /**
     * Deine bisherige Logik kannst du 그대로 behalten.
     */
    public function process() : void
    {
        $members = Member::get()->filter([
            'MarkForDeletion'              => true,
            'MarkForDeletionDate:LessThan' => date('Y-m-d'),
        ]);

        if ($members->exists()) {
            $this->printInfo("found {$members->count()} customer(s) to delete.");
            foreach ($members as $member) {
                if (!$member->canBeDeletedAutomatically()) {
                    continue;
                }
                $reason     = DeletedCustomerReason::get()->byID($member->MarkForDeletionReasonID);
                $reasonText = $member->MarkForDeletionReason;
                if ($reason instanceof DeletedCustomerReason) {
                    $reasonText = $reason->Reason;
                }
                $deleted = DeletedCustomer::create();
                $deleted->CustomerID = $member->ID;
                $deleted->ReasonID   = $member->MarkForDeletionReasonID;
                $deleted->ReasonText = $reasonText;
                $deleted->write();

                $member->delete();
                $member->sendDeletionConfirmation();
            }
        } else {
            $this->printInfo("no customers to delete.");
        }
    }
}