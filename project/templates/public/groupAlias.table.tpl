{if $groupElement = $element->getGroupElement()}
    <tr class="">
        <td>
            {$number}
        </td>
        <td>
            <a class='' href='{$element->getUrl()}'>{$element->title}</a>
        </td>
        <td><a href="{$groupElement->getUrl()}">{$groupElement->title}</a></td>
        <td>
            {if $country = $groupElement->getCountryElement()}
                <a href="{$country->getUrl('group')}">{$country->title}</a>
            {/if}
        </td>
        <td>
            {if $city = $groupElement->getCityElement()}
                <a href="{$city->getUrl('group')}">{$city->title}</a>
            {/if}
        </td>
    </tr>
{else}
    <tr class="">
        <td>
            {$number}
        </td>
        <td>
            <a class='' href='{$element->getUrl()}'>{$element->title}</a>
        </td>
        <td>missing</td>
        <td>

        </td>
        <td>

        </td>
    </tr>
{/if}