var calendars = {};

var myFunction = function() {

  
  var thisYear = $('#YearSelect').val();
  var thisMonth = moment(thisYear+"-1").format('YYYY-MM'); 
   //Need this via a feed or something  
  var eventArray = [
    { startDate: '1981-11-04', endDate: '1981-11-04', title: 'test' }
  ];

  // the order of the click handlers is predictable.
  // direct click action callbacks come first: click, nextMonth, previousMonth, nextYear, previousYear, or today.
  // then onMonthChange (if the month changed).
  // finally onYearChange (if the year changed).
  var i;
  for(i = 1; i < 14; i++) {
    $('#mn'+i).empty()
    calendars.thisMonth = $('#mn'+i).clndr({
      template: $('#template-calendar').html(),
  //    events: eventArray,
      startWithMonth: moment(thisYear+"-"+i).format('YYYY-MM'),
      clickEvents: {
        click: function(target) {
          console.log(target);
        }
      },
      showAdjacentMonths: false,
      forceSixRows: true
      
    });
    alert (moment(thisYear+"-"+i).format('YYYY-MM'));
  }

  // bind both clndrs to the left and right arrow keys
  $(document).keydown( function(e) {
    if(e.keyCode == 37) {
      // left arrow
      calendars.clndr1.back();
      calendars.clndr2.back();
    }
    if(e.keyCode == 39) {
      // right arrow
      calendars.clndr1.forward();
      calendars.clndr2.forward();
    }
  });

}
$(document).ready( myFunction);
$(function() {$('#YearSelect').change(myFunction)});