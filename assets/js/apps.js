

// Ajax project search select
function apps_ajax_projects_search(selector) {
  selector =
    typeof selector == "undefined" ? "#project_id.ajax-search" : selector;
  apps_ajax_search("project", selector);
}

// Ajax inspector_staff search select
function apps_ajax_inspector_staffs_search(selector) {
  selector =
    typeof selector == "undefined" ? "#inspector_staff_id.ajax-search" : selector;
  apps_ajax_search("inspector_staff", selector);
}


function apps_ajax_search(type, selector, server_data, url) {
  console.log(type);
  console.log(selector);

  var ajaxSelector = $("body").find(selector);

  if (ajaxSelector.length) {
    var options = {
      ajax: {
        url:
          typeof url == "undefined"
            ? site_url + "apps/get_relation_data"
            : url,
        data: function () {
          var data = {};
          data.type = type;
          data.rel_id = "";
          data.q = "{{{q}}}";
          if (typeof server_data != "undefined") {
            jQuery.extend(data, server_data);
          }
          return data;
        },
      },
      locale: {
        emptyTitle: app.lang.search_ajax_empty,
        statusInitialized: app.lang.search_ajax_initialized,
        statusSearching: app.lang.search_ajax_searching,
        statusNoResults: app.lang.not_results_found,
        searchPlaceholder: app.lang.search_ajax_placeholder,
        currentlySelected: app.lang.currently_selected,
      },
      requestDelay: 500,
      cache: false,
      preprocessData: function (processData) {
        var bs_data = [];
        var len = processData.length;
        for (var i = 0; i < len; i++) {
          var tmp_data = {
            value: processData[i].id,
            text: processData[i].name,
          };
          if (processData[i].subtext) {
            tmp_data.data = {
              subtext: processData[i].subtext,
            };
          }
          bs_data.push(tmp_data);
        }
        return bs_data;
      },
      preserveSelectedPosition: "after",
      preserveSelected: true,
    };
    if (ajaxSelector.data("empty-title")) {
      options.locale.emptyTitle = ajaxSelector.data("empty-title");
    }
    ajaxSelector.selectpicker().ajaxSelectPicker(options);
  }
}


// Ajax project search but only for specific customer
function apps_ajax_project_search_by_customer_id(selector) {
  selector =
    typeof selector == "undefined" ? "#project_id.ajax-search" : selector;
    apps_ajax_search("project", selector, {
    customer_id: function () {
      return $("#clientid").val();
    },
  });
}


// Ajax project search but only for specific customer
function apps_ajax_inspector_id_search_by_institution_id(selector) {
  selector =
    typeof selector == "undefined" ? "#inspector_id.ajax-search" : selector;
    apps_ajax_search("inspector", selector, {
    institution_id: function () {
      return $("#institution_id").val();
    },
  });
}

// Ajax project search but only for specific customer
function apps_ajax_inspector_staff_id_search_by_inspector_id(selector) {
  selector =
    typeof selector == "undefined" ? "#inspector_staff_id.ajax-search" : selector;
    apps_ajax_search("inspector_staff", selector, {
    inspector_id: function () {
      return $("#inspector_id").val();
    },
  });
}
