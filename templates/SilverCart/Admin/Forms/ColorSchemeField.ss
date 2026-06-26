<style>
    .colorscheme-label {
        display: block;
        cursor: pointer;
        border: 1px solid #dbe0e5;
        border-radius: 4px;
        margin-bottom: 8px;
        padding: 12px;
        background: #fff;
    }
    .colorscheme-label.is-active {
        border-color: #005a93;
        box-shadow: inset 3px 0 0 #005a93;
    }
    .colorscheme-radio {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
    }
    .colorscheme-colors {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 8px;
    }
    .colorscheme-color-field {
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #ddd;
        border-radius: 3px;
        padding: 6px;
        min-width: 0;
    }
    .colorscheme-color {
        display: block;
        flex: 0 0 28px;
        width: 28px;
        height: 28px;
        border: 1px solid #ccc;
        border-radius: 3px;
    }
    .colorscheme-color-label {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .colorscheme-color-value {
        display: block;
        color: #59636d;
        font-size: 11px;
    }
</style>
<% loop ColorSchemes %>
<label class="colorscheme-label<% if IsActive %> is-active<% end_if %>">
    <span class="colorscheme-radio"><input type="radio" name="ColorScheme" value="{$Name}" <% if IsActive %>checked="checked"<% end_if %>/> {$Title}</span>
    <span class="colorscheme-colors">
    <% loop Colors %>
        <span class="colorscheme-color-field">
            <span class="colorscheme-color" style="background-color: {$Color};">&nbsp;</span>
            <span>
                <span class="colorscheme-color-label">{$Label}</span>
                <code class="colorscheme-color-value">{$Color}</code>
            </span>
        </span>
    <% end_loop %>
    </span>
</label>
<% end_loop %>
