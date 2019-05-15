jQuery(document).ready(function () {
    var $ = jQuery;
    var url = document.URL.split('?')[0];
    url = url.replace(/#$/, '');

    init();

    function init() {
        $('.changesSearch').hide();
        $('.changesDedupe').hide();
        $('.changesPrepend').hide();
        $('.changesAppend').hide();
        $('.changesExplode').hide();
        $('#hide-items-button').hide();
        $('#hide-field-preview').hide();
        $('#hide-changes-preview').hide();
        $('.submit').hide();
        $('#items-waiting').hide();
        $('#fields-waiting').hide();
        $('#changes-waiting').hide();

        $('.bulk-metadata-editor-selector').keypress(function (event) {
            var key = event.which;
            // the enter key code
            if (key == 13) {
                event.preventDefault();
            }
        });

        $("#item-select-meta").change(function () {
            if (this.checked) {
                $('#item-meta').show();
            } else {
                $('#item-meta').hide();
            }
        });

        $('#select-itemset').change(function () {
            $('.submit').hide();
            $('#showSubmit').show();
        });

        $('#item-select-fields').change(function () {
            $('.submit').hide();
            $('#showSubmit').show();
        });

        $('.changesDropdown').change(function () {
            var selectedRadio = String($(this).val());
            $('.submit').hide();
            $('#showSubmit').show();
            switch(selectedRadio){
                case "replace":
                    $('.changesSearch').show();
                    $('.changesDedupe').hide();
                    $('.changesPrepend').hide();
                    $('.changesAppend').hide();
                    $('.changesExplode').hide();
                    break;
                case "deduplicate":
                    $('.changesSearch').hide();
                    $('.changesDedupe').show();
                    $('.changesPrepend').hide();
                    $('.changesAppend').hide();
                    $('.changesExplode').hide();
                    break;
                case "prepend":
                    $('.changesSearch').hide();
                    $('.changesDedupe').hide();
                    $('.changesPrepend').show();
                    $('.changesAppend').hide();
                    $('.changesExplode').hide();
                    break;
                case "append":
                    $('.changesSearch').hide();
                    $('.changesDedupe').hide();
                    $('.changesPrepend').hide();
                    $('.changesAppend').show();
                    $('.changesExplode').hide();
                    break;
                case "explode":
                    $('.changesSearch').hide();
                    $('.changesDedupe').hide();
                    $('.changesPrepend').hide();
                    $('.changesAppend').hide();
                    $('.changesExplode').show();
                    break;
                default:
                    alert("Please choose a single change type." + selectedRadio);
            }
        });

        $('#download-old').click(function (event) {
            event.preventDefault();
            $('#metadata-editor-form').attr("action", url + "/backup");
            $('#metadata-editor-form').submit();
        });

        $('#download-new').click(function (event) {
            event.preventDefault();
            $('#metadata-editor-form').attr("action", url +"/download"); 
            $('#metadata-editor-form').submit();
        });

        $('#preview-items-button').click(function (event) {
            var max = 10;
            $('#items-waiting').show();
            event.preventDefault();
            processItemRules();
            listItems(max);
        });

        $('#preview-fields-button').click(function (event) {
            var max = 5;
            $('#fields-waiting').show();
            event.preventDefault();
            processItemRules();
            listFields(max);
        });

        $('#preview-changes-button').click(function (event) {
            var max = 7;
            $('#changes-waiting').show();
            event.preventDefault();
            processItemRules();
            listChanges(max);
        });

        $('#hide-items-button').click(function (event) {
            event.preventDefault();
            $('#itemPreviewDiv').html('<br />');
            $('#hide-items-button').hide();
        });

        $('#hide-field-preview').click(function (event) {
            event.preventDefault();
            $('#fieldPreviewDiv').html('<br />');
            $('#hide-field-preview').hide();
        });

        $('#hide-changes-preview').click(function (event) {
            event.preventDefault();
            $('#changesPreviewDiv').html('<br />');
            $('#hide-changes-preview').hide();
        });

        $('#showSubmit').click(function (event) {
            var max = 200;
            event.preventDefault();
            showSubmitButton(event);
        });

         $('#showMoreItems').click(function (event) {
            var max = 200;
            event.preventDefault();
            alert("clicked");
            processItemRules();
            listItems(max);
        });
    };

    function processItemRules() {
        $('.hiddenField').remove();

        $('.bulk-metadata-editor-element-id').each(function (index) {
            var html = '<input class="hiddenField" type="hidden" name="item-rule-elements[]" value="' + $(this).val() + '" />';
            $('form').append(html);
        });

        $('.bulk-metadata-editor-compare').each(function (index) {
            var html = '<input class="hiddenField" type="hidden" name="item-compare-types[]" value="' + $(this).val() + '" />';
            $('form').append(html);
        });

        $('.bulk-metadata-editor-case').each(function (index) {
            var html = '<input class="hiddenField" type="hidden" name="item-cases[]" value="' + $(this).prop('checked') + '" />';
            $('form').append(html);
        });

        $('.bulk-metadata-editor-selector').each(function (index) {
            var html = '<input class="hiddenField" type="hidden" name="item-selectors[]" value="' + $(this).val() + '" />';
            $('form').append(html);
        });
    }


    function showMoreItems(event) {
        var max = 200;
        $('#items-waiting').show();
        event.preventDefault();
        processItemRules();
        listItems(max);
    }

    function showMoreFields(event) {
        var max = 100;
        $('#fields-waiting').show();
        event.preventDefault();
        processItemRules();
        listFields(max);
    }

    function showMoreChanges(event) {
        var max = 200;
        $('#changes-waiting').show();
        event.preventDefault();
        processItemRules();
        listChanges(max);

    }

    function showSubmitButton(event) {
        event.preventDefault();
        $('.submit').show();
        var params =  $('#metadata-editor-form').serialize();
        var output ="";
        if(! params.includes('bmeCollectionId' )){
            output = output + "No item set is selected. Please select items. <br/>";
        }
        if(! params.includes('selectFields' )){
            output = output + "No field is selected. Please select a field. <br/>";
        }
        if(params.includes('changesRadio=&' )){
            output = output + "No change is selected. Please select a change.<br/><br/>";
        }
        $('.validation').html(output + "Are you sure you want to continue?");
        $('#metadata-editor-form').attr("action", url +"/replace"); 
        $('#showSubmit').hide();
    }

    function listItems(max) {
        $.ajax({
            url: url + '/preview',
            type: "POST",
            dataType: 'json',
            data: $('#metadata-editor-form').serialize(),
            timeout: 30000,
            success: function(data){
                if (!data || data['count'] == 0){
                    alert("No items selected!");
                }else{
                    var out = '<table><tr><th>ID Number</th><th>Item Title</th><th>Description</th><th>Type</th></tr>';
                    $.each(data, function(i, n){
                        if (i < max){
                            var id = n['o:id'];
                            if('dcterms:title' in n){
                                var title = n['dcterms:title']['0']['@value'];
                            }else{
                                var title = "No title";
                            }
                            if('dcterms:description' in n){
                                var description = n['dcterms:description']['0']['@value'];
                            }else{
                                var description = "No description";
                            }
                            if('@type' in n){
                                var type = n['@type'];
                                type = type.replace('o:', '');
                            }else{
                                var type = "No type";
                            }
                            out = out + "<tr><td>" + id + "</td><td>" + title + "</td><td>" + description + "</td><td>" + type + "</td></tr>";
                        }
                    })

                    var more = data['count'] - max;
                    var sentence = "";
                    if (more > 0){
                        sentence = "</table><p>Plus " + more + ' more items. <a href="#" id="show-more-items" onclick="showMoreItems();return false;">Show more</a></p>'; 
                    }
                    $('#itemPreviewDiv').html(sentence + out );
                    $('#show-more-items').click(showMoreItems);
                    $('#hide-items-button').show();
                    }
                },

            error: function (data, errorString, error) {
                alert(errorString + " " + error);
            },
            complete: function (data, status) {
                $('#items-waiting').hide();
            }
        });
    }

    function listFields(max) {
            $.ajax({
            url: url + '/fields',
            type: "POST",
            dataType: 'json',
            data: $('#metadata-editor-form').serialize(),
            timeout: 30000,
            success: function(data){
                if (!data || data['count'] == 0){
                    alert("No matches found! Try adjusting your terms.");
                }else{
                    var matchProperties = data['matchProperties'];
                    var out = "";

                    $.each(data, function(i, n){
                        if (i < max){
                            if('dcterms:title' in n){
                                var title = n['dcterms:title']['0']['@value'];
                            }else{
                                var title = "Untitled";
                            }
                            out = out + "<table><th colspan=\"2\">" + title + "</th>";

                            $.each(matchProperties, function(j, property){
                                    var term = property['o:term'];
                                    var label = property['o:label'];
                                    if(term in n){
                                        itemProperties = n[term];
                                        out = out + "<tr><td> " + label + "</td>" +  '<td>';
                                        $.each(itemProperties, function(k, itemProperty){
                                            out = out + n[term][k]['@value'] + "<br/>";
                                        });
                                        out = out + "</td>";
                                    }
                            });
                            out = out + "</table>"
                        }
                    });
                    var more = data['count'] - max;
                    var sentence = "";
                    if (more > 0){
                        sentence = "<p>Plus " + more + ' more items. <a href="#" id="show-more-fields" onclick="showMoreFields();return false;">Show more</a></p>'; 
                    }
                    var alt = data['alt'];
                    $('#fieldPreviewDiv').html(sentence + out);
                    $('#show-more-fields').click(showMoreFields);
                    $('#hide-field-preview').show();
                }
            },
            error: function (data, errorString, error) {
                alert(errorString + " " + error + " " + JSON.stringify(data));
            },
            complete: function (data, status) {
                $('#fields-waiting').hide();
            }
        });
    }

    function listChanges(max) {
        $.ajax({
            url: url + '/changes',
            type: "POST",
            dataType: 'json',
            data: $('#metadata-editor-form').serialize(),
            timeout: 30000,
            success: function(data){
                if (!data || data['count'] == 0){
                    alert("No matches found! Try adjusting your terms.");
                }else{
                    var matchProperties = data['matchProperties'];
                    var out = "<table><tr><th>Item</th><th>Property</th><th>Old Value</th><th>New Value</th></tr>";
                    var count = 0;

                    $.each(data, function(i, item){
                        if (i < max){

                            $.each(matchProperties, function(j, property){
                                    var term = property['o:term'];
                                    var label = property['o:label'];
                                        if(term in item){
                                            if('newItem' in item){
                                                itemProperties = item[term];
                                                newProperties = item['newItem'][term];
                                                count = count + 1;
                                                if(JSON.stringify(itemProperties) !== JSON.stringify(newProperties)){
                                                    if('dcterms:title' in item){
                                                        var title = item['dcterms:title']['0']['@value'];
                                                    }else{
                                                        var title = "Untitled";
                                                    }
                                                    out = out + "<tr><td>" + title + "</td>";
                                                    out = out + "<td> " + label + "</td>" +  '<td>';
                                                    $.each(itemProperties, function(k, itemProperty){
                                                        out = out + item[term][k]['@value'] + "<br/>";
                                                    });
                                                     
                                                    out = out + '</td>' + "<td>";
                                                    
                                                    $.each(newProperties, function(l, newProperty){
                                                         out = out + newProperty['@value'] + "<br/>";
                                                    });
                                                    out = out + "</td>";
                                                }
                                            }
                                        }
                                        out = out + "</tr>";
                            });
                            
                        }
                    });
                    var more = count - max;
                    var sentence = "";
                    if (more > 0){
                        sentence = "</table><p>Plus " + more + ' more items. <a href="#" id="show-more-changes" onclick="showMoreChanges();return false;">Show more</a></p>'; 
                    }
                    var alt = count;
                    $('#changesPreviewDiv').html(sentence + out);
                    $('#show-more-changes').click(showMoreChanges);
                    $('#hide-changes-preview').show();
                }
            },

            error: function (data, errorString, error) {
                alert(errorString + " " + error);
            },
            complete: function (data, status) {
                $('#changes-waiting').hide();
            }
        });
    }
});
