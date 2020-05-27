// all tags value 
let _tags_values = null;

// max tags limit
var _max_tag_num = ("undefined" != typeof _max_tag_num) ? _max_tag_num : 3;

// current input tag value
let _current_input_value = null;

/*
create a chainnable method for the script to
*/
$.fn.tagsValues = function(method /*, args*/ ) {
    //loop through all tags getting the attribute value
    let data = [];
    $(this).find(".data .tag").each(function(key, value) {
        let v = $(value).text();
        data.push(v);
    })

    return data;
};

// add input tag
function _add_input_tag(el, data) {
    let tags_input = $(el).closest(".tags-input");

    // data  already exists
    if ("-1" != _tags_values.indexOf(data)) {
        $(tags_input).find("input[type=text]").val("");
        return false;
    }

    //  limit max tags
    if (_tags_values.length >= _max_tag_num) {
        console.log("_max_tag_num：" + _max_tag_num);
        return false;
    }

    let template = "<span class=\"tag is-medium is-danger is-light\">" + data + "<span class=\"delete\"></span></span>\n";
    $(tags_input).find(".data").append(template);
    $(tags_input).find("input[type=text]").val("");

    // 同步 values
    _synchronize_values(el);
}

// _synchronize_values
function _synchronize_values(el) {
    let tags_input = $(el).closest(".tags-input");
    _tags_values = tags_input.tagsValues();
    console.log(_tags_values);

    // set values to the hidden input field so we can submit the form
    $(tags_input).find("input[type=hidden]").val(_tags_values);

    // limit max tags
    if (_tags_values.length >= _max_tag_num) {
        tags_input.find("input").prop("readonly", true);
        tags_input.find(".autocomplete-items").html("");
        return false;
    } else {
        tags_input.find("input").prop("readonly", false);
        return false;
    }
}

/**
 *using ajax to populate suggestions
 *Modify this function to suit your actual application scenario
 */
function _run_suggestions(el, query) {
    let sug_area = $(el).closest(".tags-input").find(".autocomplete-items");
    sug_area.html(""); // empty suggestion

    if (!query) {
        return [];
    }

    let data = {
        "data": "wnd_term_searcher",
        "param": {
            "search": query,
            "taxonomy": $(el).data("taxonomy")
        }
    };

    $.ajax({
        type: "GET",
        url: wnd_jsonget_api,
        data: data,
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-WP-Nonce", wnd.rest_nonce);
        },

        //  data format array ['tag1','tag2','tag3']
        success: function(data) {
            $.each(data, function(key, value) {
                if ("-1" == value.indexOf(query)) {
                    return true;
                }

                let template = $('<li class="suggest-items">' + value + '</li>').hide();
                sug_area.append(template);
                template.show();
            })
        }
    });
}

jQuery(document).ready(function($) {
    // _synchronize_values on document loaded
    _synchronize_values(".tags-input");

    /*
    Handle click of the input area
     */
    $(document).on("click", ".tags-input", function() {
        $(this).find("input").focus();
    });

    /*
    handle the click of close button on the tags
     */

    $(document).on("click", ".tags-input .data .tag .delete", function() {
        let tags_input = $(this).closest(".tags-input");

        // whatever you do to delete this row
        $(this).parent().remove();

        _synchronize_values(tags_input);
    })

    /*
    Handle the click of one suggestion
    */

    $(document).on("click", ".tags-input .autocomplete-items li", function() {
        let tags_input = $(this).closest(".tags-input");
        let data = $(this).text();
        let data_holder = tags_input.find(".data");
        _add_input_tag(data_holder, data, data);

        // 同步value
        _synchronize_values($(this));

        tags_input.find(".autocomplete-items").html("");
    })

    /*
    detect enter on the input
     */
    $(document).on("keydown", ".tags-input input", function(event) {
        // 监听键盘：回车，中英文逗号
        if (
            event.which == 13 ||
            event.which == 188 ||
            ("-1" != $(this).val().indexOf("，"))
        ) {
            let data = $(this).val();
            data = $(this).val().replace("，", "");
            data = data.replace(",", "");
            $(this).val(data);

            if (data != "") {
                _add_input_tag(this, data);
            }
        }

    });

    // handle input key up：query suggestion tags
    $(document).on("keyup", ".tags-input input", function(event) {
        if ($(this).prop("readonly")) {
            return false;
        }

        // 检测input值是否有改变
        if (_current_input_value == $(this).val()) {
            return false;
        } else {
            _current_input_value = $(this).val();
        }

        if ("function" == typeof _run_suggestions) {
            _run_suggestions(this, _current_input_value);
        }
    });
});