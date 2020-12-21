// function SubmitFormData() {

//   $.post("submit.php", {},
//   function(data) {
//  $('#results').html(data);
//  $('#myForm')[0].reset();
//   });
// }


function SubmitFormData() {
  var inputInfo = $("#words").val();
  var regionID = $("input[type=radio]:checked").val();
  $.post("submit.php", { inputInfo : inputInfo , regionID : regionID },
  function(data) {
 $('#results').html(data);
 $('#myForm')[0].reset();
  });
}