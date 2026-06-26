<% if $Checkout && $Checkout.CheckoutSteps %>
    <% with $Checkout %>
<table class="table checkout-steps">
    <tr>
    <% if $ShowCartInCheckoutNavigation %>
        <td <% if $CurrentPageIsCartPage %>class="current-step"<% end_if %>>
            <div class="well">
                <% if $CurrentPageIsCartPage %>
                <span class="highlight active"><strong><span class="icon-shopping-cart"></span> 1. <span class="step-title"><%t SilverCart\Model\Pages\Page.CART 'Cart' %></span></strong></span>
                <% else %>
                <a class="highlight" href="{$Top.PageByIdentifierCode(SilvercartCartPage).Link}"><span class="icon-ok"></span> <span class="icon-shopping-cart"></span> 1. <span class="step-title">{$CurrentUser.Cart.singular_name}</span></a>
                <% end_if %>
            </div>
        </td>
    <% end_if %>
    </tr>
</table>
    <% end_with %>
<% end_if %>