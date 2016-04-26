/**
 * Created by erik on 13-12-15.
 */
/**
 * Function getSegmentChildren
 *
 * retrieve segment children for parent id passed to function with api and set options for select list
 * @param parentId
 */
function getSegmentChildren(parentId) {
  cj("#segment_child option").remove();
  cj("#segment_child").append("<option value=0>- select -</option>");
  CRM.api('Segment', 'Get', {"parent_id":parentId}, {
    success: function(data) {
      cj.each(data, function(key, value) {
        if (key == "values") {
          cj.each(value, function(segmentKey, segmentValue) {
            cj("#segment_child").append("<option value=" + segmentKey + ">" + segmentValue.label + "</option>");
          })
        }
      });
    },
    error: function() {
      CRM.alert("Could not find any segment data for id " + parentId + ", contact your system administrator", "No Segment", "error");
    }
  });
}

