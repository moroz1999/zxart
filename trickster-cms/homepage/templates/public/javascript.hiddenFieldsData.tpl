<script>

    if(typeof window.hiddenFieldsData == 'undefined') {
        window.hiddenFieldsData = new Array();
    }
    window.hiddenFieldsData.push(json_encode({$element->getHiddenFieldsData()}));

</script>