(function($, window, document) {

    //
    // Globals
    var pluginName = 'calendar',
    pl = null,
    d = new Date();

    //
    // Defaults
    defaults = {
        d: d,
        year: d.getFullYear(),
        today: d.getDate(),
        month: d.getMonth(),
        current_year: d.getFullYear(),
        tipsy_gravity: 's',
        scroll_to_date: true
    };

    month_array = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December'
    ];

    month_days = [
    '31', // jan
    '28', // feb
    '31', // mar
    '30', // apr
    '31', // may
    '30', // june
    '31', // july
    '31', // aug
    '30', // sept
    '31', // oct
    '30', // nov
    '31'  // dec
    ];
        
    week_days = [
    'Su', 
    'Mo', 
    'Tu', 
    'We', 
    'Th', 
    'Fr', 
    'Sa'  
    ];
        
       

    //
    // Main Plugin Object
    function Calendar(element, options) { 
        pl = this;
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;              

        //
        // Begin
        this.init();
    }

    //
    // Init
    Calendar.prototype.init = function() {

        //
        // Call print - who knows, maybe more will be added to the init function...
        this.print();
    }

    Calendar.prototype.print = function(year) {

        //
        // Pass in any year you damn like.
        var the_year = (year) ? parseInt(year) : parseInt(pl.options.year);
                
        // First, clear the element
        $(this.element).empty();


        //
        // Append parent div to the element
        $(this.element).append('<div id=\"calendar\"></div>');
        //
        // Set reference for calendar DOM object
        var $_calendar = $('#calendar');
		
        // Add year dropdown
        var year_array  =  pl.options.year_array;
                
        var req_ajax;
        if ( typeof req_ajax != "undefined" )
            req_ajax = req_ajax;
        else
            req_ajax = false;

                
        $_calendar.append("<div class='input-group'> <span class='input-group-addon'><b>Select a publication year:</b></span><select name='SelectYear' id='SelectYear' class='form-control'></select></div><br/>");
        var $_year = $('#SelectYear');
                
        $.each(year_array, function(i, o) {
            var selected = "";
            if( year_array[i] == the_year )
                selected = "selected";
                       
            $_year.append("<option value="+year_array[i]+" "+selected+">"+year_array[i]+"</option>");
        });
                
              
                
        // Add a clear for the floated elements
        $_calendar.append('<div class=\"clear\"></div>');

		
        // Loop over the month arrays, loop over the characters in teh string, and apply to divs.
        $.each(month_array, function(i, o) {
            // Create a scrollto marker
			
		
            $_calendar.append('<div id="month'+i+'" class="month"></div>');
                        
            // DOM object reference for month
            $_amonth = $('#month'+i);
            $_amonth.append("<div id='" + o + "' class='header well well-primary well-sm'>"+o+"</div>");
            $.each(week_days, function(i, o) {
                $_amonth.append("<div class='days "+week_days[i]+"'>"+week_days[i]+"</div>");
            });
                        

            // Add a clear for the floated elements
            $_amonth.append('<div class=\"clear\"></div>');

            //
            // Check for leap year
            if (o === 'February') {
                if (pl.isLeap(the_year)) {
                    month_days[i] = 29;
                } else {
                    month_days[i] = 28;
                }
            }

            for (j = 1; j <= 43; j++) {
                if (j == 1 ) {
                    var d = new Date((parseInt(i) + 1) + '/' + j + '/' + the_year );
                    var da = d.getDay();
                    for (m = 1; m <= da; m++) {
                        $_amonth.append("<div class='day'></div>");
                    }
                }
                //
                // Check for today
                var today = '';
                if (i === pl.options.month && the_year === d.getFullYear()) {
                    if (j === pl.options.today) {
                        today = 'today';
                    }
                }
                               
                var current_date = (parseInt(i) + 1) + '/' + j + '/' + the_year; 
                               
				
                // Looping over numbers, apply them to divs
                if(j <= parseInt(month_days[i])) {
                    $_amonth.append("<div data-date='" + current_date + "' class='day " +pl.returnFormattedDate(current_date)+"'>" + j + '</div>');
                } else {
                    $_amonth.append("<div class='day'>&nbsp;</div>"); 
                                 
                } 
            }
			
        });                
                  
        pl.update_items(the_year);
    }

    //Update the calendar when the Year selection changes
    $(document).on('change', '#SelectYear', function() {
        pl.options.year = parseInt($(this).val());
        pl.print(pl.options.year);
    }); 
                      
    //
    // Simple JS function to check if leap year
    Calendar.prototype.isLeap = function(year) {
        var leap = 0;
        leap = new Date(year, 1, 29).getMonth() == 1;
        return leap;
    }

    //
    // Method to return full date
    Calendar.prototype.returnFormattedDate = function(date) {
        var returned_date;
        var d = new Date(date);
        var da = d.getDay();

        if (da === 1) {
            returned_date = 'Mo';
        } else if (da === 2) {
            returned_date = 'Tu';
        } else if (da === 3) {
            returned_date = 'We';
        } else if (da === 4) {
            returned_date = 'Th';
        } else if (da === 5) {
            returned_date = 'Fr';
        } else if (da === 6) {
            returned_date = 'Sa';
        } else if (da === 0) {
            returned_date = 'Su';
        }

        return returned_date;
    }
    
    // Populate the Items in the calendar    
    Calendar.prototype.update_items = function (this_year){
      
        $.ajax({
            type: pl.options.req_ajax.type,
            url: pl.options.req_ajax.url,
            data: {
                year: this_year, 
                TitleLink: pl.options.req_ajax.TitleLink
            },
            dataType: 'json',
            beforeSend: function() {
                $('#loader').modal('show').css({
                        'margin-top': function () { //vertical centering
                            return ($(this).height() / 3);
                        }
                    });  
                                 },
             complete: function(){
                $('#loader').modal('hide');
             }
        }).done(function( data ) {
            item_array = data;                          
            for (k = 0; k < $('.day').length; k++) {
                (function(j) {

                    setTimeout(function() {

                        // Fade the labels in
                        $($('.day')[j]).fadeIn('fast', function() {

                            var attr = $(this).attr('data-date');

                            if( typeof attr !== 'undefined' && attr !== false && attr in item_array ) {
                              
                                // Set the IID information to the DOM
                                
                                if ( item_array[$(this).attr('data-date')].length > 1) {
                                    $items = item_array[$(this).attr('data-date')];
                                    tooltip = "";
                                    for (n = 0; n < $items.length; n++) {
                                        tooltip += $items[n]['text']+"</br>" ;
                                    }    
                                
                                    $(this).attr('data-container', 'body' );
                                    $(this).attr('data-toggle', 'popover' );
                                    $(this).attr('data-content', tooltip );
                                    $(this).attr('data-html', "true" );
                                   
                                } else {
                                
                                    $(this).attr('data-iid', item_array[$(this).attr('data-date')][0]['iid']);
                                }
                                $(this).addClass('filled');
                                $(this).css({
                                    "color":"#555",
                                    "font-weight":'bold',
                                    "cursor":"pointer"
                                });
                               
                                
                                $(this).on('click', function() {
                                    if($(this).attr('data-toggle')) {
                                        $(this).popover('show');
                                        return;
                                    }
                                    if (typeof pl.options.click_callback == 'function') {
                                        var dObj = {}
                                        dObj.date = $(this).attr('data-date');
                                        dObj.iid = $(this).attr('data-iid');
                                        pl.options.click_callback.call(this, dObj);
                                    }
                                });
                            }

                        });

                    }, (k * 1));
                })(k);
            }
       

        }).fail(function (jqXHR, textStatus) {
                //Do something here
            });
   
    }

    //
    // Plugin Instantiation
    $.fn[pluginName] = function(options ) {
        return this.each(function() {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName, new Calendar(this, options));
            }
        });
    }
    
 
            

})(jQuery, window, document);
