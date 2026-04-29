{if isset($currentElementPrivileges.import)}
    <form class="export_import_form form_component" action="{$currentElement->URL}id:{$currentElement->id}/action:import/" method="post" enctype="multipart/form-data">
        <table class="form_table">
            <tr{if $formErrors.xmlFile} class="form_error"{/if}>
                <td class="form_label">
                    {translations name="{$translationGroup}.{strtolower($fieldName)}"}:
                </td>
                <td>
                    <input class="fileinput_placeholder" type="file" name="{$formNames.xmlFile}"/>
                </td>
                <td>
                    <input class="actions_form_import button" type="submit" value='{translations name="$structureType.upload"}'/>
                </td>
            </tr>
        </table>
    </form>
{/if}