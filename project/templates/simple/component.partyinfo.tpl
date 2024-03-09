<b>{translations name='field.title'}: </b> {$element->title}<br>
{if $element->abbreviation}<b>{translations name='field.abbreviation'}:	</b>{$element->abbreviation}<br>{/if}
<b>{translations name='field.year'}:</b> {$element->getYear()}<br>
{if $element->getCityTitle()}<b>{translations name='field.city'}: </b> {$element->getCityTitle()}<br>{/if}
{if $element->getCountryTitle()}<b>{translations name='party.country'}:	</b>{$element->getCountryTitle()}<br>{/if}