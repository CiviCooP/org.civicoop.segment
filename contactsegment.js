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
  var inactiveLabel = " (inactive) ";
  cj("#segment_child option").remove();
  cj("#segment_child").append("<option value=0>- select -</option>");
  CRM.api('Segment', 'Get', {"parent_id":parentId, "is_active":1}, {
    success: function(data) {

      var ar_segment = cj.map(data, function(value, index) {
          return [value];
      });

      var ar_aoe = cj.map(ar_segment[3],function(value,index) {
          return [value];
      });

      ar_aoe.sort(function(a,b) {return (a.label > b.label) ? 1 : ((b.label > a.label) ? -1 : 0);} );

      cj.each(ar_aoe, function(segmentKey, segmentValue) {
          cj("#segment_child").append("<option value=" + segmentValue.id + ">" + segmentValue.label + "</option>");
      });
    },
    error: function() {
      CRM.alert("Could not find any segment data for id " + parentId + ", contact your system administrator", "No Segment", "error");
    }
  });
}