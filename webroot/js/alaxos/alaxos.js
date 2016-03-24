var Alaxos = (function($j) {
	
	var INVALID_DATE             = "INVALID_DATE";
	var DEFAULT_DATE_FORMAT      = "y-m-d";
	
	var pleaseSelectAtLeastOneItem = "Please choose at least one item from the list";
	
	/********************************************************************
     * Date formatting
     */
	
	function get_date_format(date_format)
	{
		if(typeof(date_format) == "undefined" || date_format == null || date_format.length == 0)
		{
			date_format = Alaxos.DEFAULT_DATE_FORMAT;
		}
		else
		{
			date_format = date_format.replace(/dd/g, 'd');
			date_format = date_format.replace(/mm/g, 'm');
			date_format = date_format.replace(/yyyy/g, 'y').replace(/yyy/g, 'y').replace(/yy/g, 'y');
		}
		
		return date_format;
	}
	
	function get_complete_date_object(date_str, date_format)
	{
		date_format = Alaxos.get_date_format(date_format);
		
		var exploded_date_parts  = Alaxos.explode_date_parts(date_str, date_format);
		var completed_date_parts = Alaxos.get_date_parts(exploded_date_parts, date_format)
		
		var year  = completed_date_parts["year"];
		var month = completed_date_parts["month"] -1; //month is zeroo based in Date object
		var day   = completed_date_parts["day"];
		
		return new Date(year, month, day);
	}
	
	function explode_date_parts(date_str, date_format)
	{
		if(typeof(date_format) == "undefined" || date_format == null || date_format.length == 0)
		{
			date_format = Alaxos.DEFAULT_DATE_FORMAT;
		}
		
		var separator1 = '';
		var separator2 = '';
		var value1 = '';
		var value2 = '';
		var value3 = '';
	
		previous_value = '';
		typed_index = 0;
		var i;
		for(i = 0; i < date_str.length; i++)
		{
			current_char = date_str.charAt(i);
			
			if(i == 0 || (previous_value == ' ' && !isNaN(current_char)) || (current_char == ' ' && !isNaN(previous_value)) || (isNaN(current_char) && !isNaN(previous_value)) || (!isNaN(current_char) && isNaN(previous_value)))
			{
				//change from number to separator or from separator to number
				typed_index++;
	
				//manage the case of a value starting with a separator
				if(i == 0 && isNaN(current_char))
				{
					typed_index = 2;
				}
			}
	
			switch(typed_index)
			{
				case 1:
					value1 += '' + current_char;
					break;
	
				case 2:
					separator1 += '' + current_char;
					break;
	
				case 3:
					value2 += '' + current_char;
					break;
	
				case 4:
					separator2 += '' + current_char;
					break;
	
				case 5:
					value3 += '' + current_char;
					break;
			}
	
			previous_value = current_char;
		}
		
		return {
			value1		: 	value1,
			separator1	:	separator1,
			value2		: 	value2,
			separator2	:	separator2,
			value3		: 	value3,
		}
	}
	
	function get_date_parts(exploded_date_parts, date_format)
	{
		var date_part1 = date_format.substring(0, 1);
		var separator1 = date_format.substring(1, 2);
		var date_part2 = date_format.substring(2, 3);
		var separator2 = date_format.substring(3, 4);
		var date_part3 = date_format.substring(4, 5);
		
		var date_part_value1 = Alaxos.get_date_part_value(exploded_date_parts["value1"], date_part1);
		var date_part_value2 = Alaxos.get_date_part_value(exploded_date_parts["value2"], date_part2);
		var date_part_value3 = Alaxos.get_date_part_value(exploded_date_parts["value3"], date_part3);
	
		var day = null;
		var month = null;
		var year = null;
		switch(date_part1)
		{
			case 'd':
				day = date_part_value1;
				break;
			case 'm':
				month = date_part_value1;
				break;
			case 'y':
				year = date_part_value1;
				break;
		}
		switch(date_part2)
		{
			case 'd':
				day = date_part_value2;
				break;
			case 'm':
				month = date_part_value2;
				break;
			case 'y':
				year = date_part_value2;
				break;
		}
		switch(date_part3)
		{
			case 'd':
				day = date_part_value3;
				break;
			case 'm':
				month = date_part_value3;
				break;
			case 'y':
				year = date_part_value3;
				break;
		}
		
		return {
			year	: 	year,
			month	:	month,
			day		: 	day
		}
	}
	
	function get_date_part_value(value, date_part)
	{
		switch(date_part)
		{
			case 'd':
				return Alaxos.get_checked_day(value);
				break;
			case 'm':
				return Alaxos.get_checked_month(value);
				break;
			case 'y':
				return Alaxos.get_checked_year(value);
				break;
		}
	}
	
	function get_checked_day(value)
	{
		day = null;
		if(value != null && value.length > 0 && !isNaN(value) && value >= 1 && value <= 31)
		{
			day = value;
		}
		else
		{
	    	date = new Date();
	    	day = date.getDate();
		}
	
		if(day < 10 && day.length != 2)
		{
			day = day * 1; //manage the case of leading zeros (e.g. '0007')
			day = '0' + day;
		}
	
		return day;
	}
	
	function get_checked_month(value)
	{
		month = null;
		if(value != null && value.length > 0 && !isNaN(value) && value >= 1 && value <= 12)
		{
			month = value;
		}
		else
		{
	    	date = new Date();
	    	month = date.getMonth();
	    	month = month + 1;
		}
	
		if(month < 10 && month.length != 2)
		{
			month = month * 1; //manage the case of leading zeros (e.g. '0007')
			month = '0' + month;
		}
	
		return month;
	}
	
	function get_checked_year(value)
	{
		if(value != null && value.length > 0 && !isNaN(value))
		{
			complete_year = Alaxos.get_complete_year(value);
	
			current_date = new Date();
			current_year = current_date.getFullYear();
			year_diff = complete_year - current_year;
	
			if(year_diff > 15)
			{
				return Alaxos.get_complete_year(complete_year - 100);
			}
			else
			{
				return complete_year;
			}
		}
		else
		{
			date = new Date();
			year = date.getFullYear();
			return year;
		}
	}
	
	function get_complete_year(year)
	{
		date = new Date(year, 1, 1);
	
		full_date = date.getFullYear();
	
		if(year.length < 4 && full_date < 2000)
		{
			full_date += 100;
		}
		
		return full_date;
	}
	
	function check_date_validity(day, month, year)
	{
		if(month == 2 && day > 28)
		{
			return Alaxos.is_bissextile(year);
		}
		else
		{
			if(!isNaN(day) && day > 0 && day < 31)
			{
				return true;
			}
			else if(day == 31)
			{
				if(month == 1 || month == 3 || month == 5 || month == 7 || month == 8 || month == 10 || month == 12)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
	}
	
	function is_bissextile (year)
	{
		// February has 29 days in any year evenly divisible by four,
	    // EXCEPT for centurial years which are not also divisible by 400.
	    return (year % 4 == 0) && ( (!(year % 100 == 0)) || (year % 400 == 0));
	}
	
//	function date_field(dom_id, language, format)
//	{
//		language = "fr";
//		format   = "d.m.y"
//		
//		$(dom_id).datepicker({language : language, forceParse : false, autoclose : true, todayHighlight: true, showOnFocus : false});
//
//		$(dom_id).blur(function(){
//			var value = $(this).val();
//			if(value != null && value.length > 0){
//				var completed_date = Alaxos.get_complete_date_object(value, format);
//			}
//			
//			date_on_blur_timeout = setTimeout(function(){
//				
//				if($(".datepicker:visible").length == 0){
//					$(dom_id).datepicker("setDate", completed_date);
//				}
//				
//			}, 30);
//		});
//		
//		$(dom_id).keypress(function(e){
//			
//			var value = $(this).val();
//			
//			if(value != null && value.length > 0){
//				
//				if(e.which == 13){
//					
//					$(dom_id).datepicker("hide");
//					
//					var completed_date = Alaxos.get_complete_date_object(value, format);
//					
//					$(dom_id).datepicker("setDate", completed_date);
//					
//					var newvalue = $(this).val();
//					
//					if(newvalue != value)
//					{
//						e.preventDefault();
//					}
//				}
//			}
//		});
//		
//		$(dom_id).datepicker().on("changeDate", function(){
//			
//			clearTimeout(date_on_blur_timeout);
//			
//		});
//
//		$(dom_id + "-group-addon").click(function(e){
//			
//			$(dom_id).datepicker("show");
//			
//		});
//	}
	
	/********************************************************************
     * 
     */
	function time_field(dom_id, callback)
	{
		$j(dom_id).blur(function(e){
			Alaxos.format_time(this, false);
			
			if(typeof(callback) != "undefined"){
				callback.call(this);
			}
		});
	}
	
	function format_time(textbox, with_seconds)
	{
	    var value = $j(textbox).val();
	    
	    if(value.length == 0)
	    {
	        return;
	    }
	    else if(value.length == 3)
	    {
	        value = value.substring(0, 2) + ":" + value.substring(2, 3);
	    }
	    else if(value.length == 4)
	    {
	        value = value.substring(0, 2) + ":" + value.substring(2, 4);
	    }
	    else if(value.length == 6)
	    {
	        value = value.substring(0, 2) + ":" + value.substring(2, 4) + ":" + value.substring(4, 6);
	    }
	    
	    var numbers = "0123456789";
	    
	    var hour = "";
	    var min  = "";
	    var sec  = "";
	    var separators_found = 0;
	    var last_is_separator = false;
	    for(var i = 0; i < value.length; i++)
	    {
	        if(numbers.indexOf(value.charAt(i)) != -1)
	        {
	            last_is_separator = false;
	            
	            if(separators_found == 0)
	            {
	                hour += value.charAt(i);
	            }
	            else if (separators_found == 1)
	            {
	                min += value.charAt(i);
	            }
	            else if (separators_found == 2)
	            {
	                sec += value.charAt(i);
	            }
	        }
	        else
	        {
	            if(last_is_separator == false)
	            {
	                separators_found++;
	            }
	            
	            last_is_separator = true;
	        }
	    }
	    
	    newValue = "";
	    
	    if(hour.length > 0)
	    {
	        hour         = parseInt(hour, 10);
	        var hour_str = hour + "";
	        
	        if(hour > 24)
	        {
	            hour     = hour % 24 + "";
	            hour_str = hour + "";
	        }
	        
	        if(hour < 10 && hour_str.length < 2)
	        {
	            hour = "0" + hour;
	        }
	    }
	    else
	    {
	        hour = "00";
	    }
	    
	    newValue += hour + ":"; 
	    
	    if(min.length > 0)
	    {
	        min         = parseInt(min, 10);
	        var min_str = min + "";
	        
	        if(min > 60)
	        {
	            min     = min % 60;
	            min_str = min + "";
	        }
	        
	        if(min < 10 && min_str.length < 2)
	        {
	            min = "0" + min;
	        }
	    }
	    else
	    {
	        min = "00";
	    }
	    
	    newValue += min;
	    
	    if(with_seconds)
	    {
	        if(sec.length > 0)
	        {
	            if(sec > 60)
	            {
	                sec = sec % 60 + "";
	            }
	            
	            if(sec < 10 && sec.length < 2)
	            {
	                sec = "0" + sec;
	            }
	        }
	        else
	        {
	            sec = "00";
	        }
	        
	        newValue += ":" + sec;
	    }
	    
	    $j(textbox).val(newValue);
	}
	
	/********************************************************************
     * Alaxos Datetime widget
     */
	function formatDateAndUpdateHiddenField(fieldname, date_id, format, callback)
	{
		var date_fieldname   = fieldname + "__date__";
		
		var value = $('[name="' + date_fieldname + '"]').val();
		
		var completed_date = null
		
		if(value != null && value.length > 0){
			var completed_date = Alaxos.get_complete_date_object(value, format);
		}
		
		/*
		 * wait a bit before setting the new value --> allow the datepicker to...
		 */
		date_on_blur_timeout = setTimeout(function(){
			
			if($(".datepicker:visible").length == 0){
				$("#" + date_id).datepicker("setDate", completed_date);
			}
			
			Alaxos.updateDatetimeHiddenField(fieldname);
			
			if(typeof(callback) != "undefined"){
				callback();
			}
			
		}, 30);
	}
	
	function updateDatetimeHiddenField(fieldname)
	{
		var date_fieldname   = fieldname + "__date__";
		var time_fieldname   = fieldname + "__time__";
		var hidden_fieldname = fieldname;
		
		var date = $('[name="' + date_fieldname + '"]').val();
		var time = $('[name="' + time_fieldname + '"]').val();
		
		if(date.length == 0 || time.length == 0){
			$('[name="' + hidden_fieldname + '"]').val("");
		}else{
			$('[name="' + hidden_fieldname + '"]').val(date + " " + time);
		}
	}
	
	/********************************************************************
     * 
     */
	
	function number_field(dom_id, allow_decimal)
	{
		$j(dom_id).keypress(function(e){
			
			var w = e.which;
			
			if(e.ctrlKey){
				return true;
			}
			
			/*
			 * Allows arrows + back + enter keys
			 */
			var ignored_codes = [0, 8, 13];
			if ($j.inArray(w, ignored_codes) != -1){
				return true;
			}
			
			/*
			 * Allow numbers and minus sign
			 */
		    var allowed_keycodes = [48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 45];
		    /*
		    0 	48
		    1 	49
		    2 	50
		    3 	51
		    4 	52
		    5 	53
		    6 	54
		    7 	55
		    8 	56 
		    9	57
		    
		    -	45
		    .   46
		    */
		    
		    if(allow_decimal){
		    	allowed_keycodes.push(46);
		    }
		    
		    var valid = false;
		    
		    if ($j.inArray(w, allowed_keycodes) != -1){
		        valid = true;
		    }
		    
		    var value = $j(dom_id).val();
		    if(value == "" && w == 45){
		    	valid = true;
		    }
		    
		    if(!valid){
		    	e.preventDefault();
		    }
			
//			$j(dom_id).after("<div style=\"color:red;\">" + e.which + "</div>");
//			$j(dom_id).after("<div style=\"color:inherit;\">" + $(this).val() + "</div>");
			
		}).keyup(function(e){
//			$j(dom_id).after("<div style=\"color:green;\">" + $(this).val() + "</div>");
			
			/*
			 * Ensure minus sign is only entered as the first char
			 */
			$(this).val(Alaxos.format_number($(this).val(), allow_decimal));
		});
	}
	
	function format_number(value, is_float)
	{
		var valid_numeric_chars = "0123456789";
		if(is_float)
		{
			valid_numeric_chars += ".";
		}
		
		newvalue          = "";
		non_numeric_found = false;
		dot_found         = false;
		var i;
		for(i = 0; i < value.length; i++)
		{
			if(valid_numeric_chars.indexOf(value.charAt(i)) != -1)
			{
				if(dot_found == false || value.charAt(i) != ".")
				{
					newvalue += value.charAt(i);
				}
				
				if(value.charAt(i) == ".")
				{
					dot_found = true;
					non_numeric_found = true; 
				}
			}
			else if(i == 0 && value.charAt(i) == "-")
			{
				newvalue += value.charAt(i);
			}
			else
			{
				non_numeric_found = true;
			}
		}
		
		return newvalue;
	} 
	
	/********************************************************************
     * 
     */
	
	function register_rows_dblclick(){
		
		/*
		 * Add double click navigation to table rows if a link with class="to_view" exists in the row
		 */
		$j("tr a.to_view").each(function(){
			var link = $j(this);
			$j(this).parents("tr").bind("dblclick", function(){
				window.location = $j(link).attr("href");
			});
		});
	}
	
	function register_select_all_checkboxes(){
		/*
		 * Select/Unselect all rows by clicking on the checkbox
		 */
		$j("#TechSelectAll").click(function(){
			
			$j("input.model_id[type=checkbox]").each(function(){
				
				$j(this).prop("checked", ($j("#TechSelectAll:checked").length > 0));
				
				Alaxos.set_row_status($j(this));
			});
			
			Alaxos.check_action_all_btns_state();
		});
	}
	
	function register_select_row(){
		
		/*
		 * Select row by clicking on it 
		 */
		$j("table > tbody > tr > td:not(.actions)").click(function(e){
			
			var checkbox = $j(this).parent().find("td").find("input.model_id[type=checkbox]");
			if($j(checkbox).prop("checked"))
			{
				$j(checkbox).prop("checked", false);
			}
			else
			{
				$j(checkbox).prop("checked", true);
			}
	
			Alaxos.set_row_status($j(checkbox));
			Alaxos.check_action_all_btns_state();
		});
		
		/*
		 * Prevent conflict between click on row and click on checkbox in the row 
		 */
		$j("input.model_id[type=checkbox]").click(function(e){
			e.stopPropagation();
			
			Alaxos.set_row_status($j(this));
			Alaxos.check_action_all_btns_state();
		});
	}
	
	function set_row_status(checkbox){
		/*
		 * Add an 'info' CSS class to the row if its checkbox is checked
		 */
		if(!$j(checkbox).parents("tr").hasClass("filterHeader"))
		{
			if($j(checkbox).prop("checked"))
			{
				$j(checkbox).parents("tr").addClass("info");
			}
			else
			{
				$j(checkbox).parents("tr").removeClass("info");
			}
		}
	}
	
	function check_action_all_btns_state()
	{
		if($j("input.model_id[type=checkbox]:checked").length > 0){
			$j(".action_all_btn").removeProp("disabled");
		}
		else{
			$j(".action_all_btn").prop("disabled", "disabled");
		}
	}
	
	function register_action_all_btns(){
		
		$j(".action_all_btn").click(function(e){
			
			e.stopPropagation();
			e.preventDefault();
			
			var checked_ids = [];
			$j("input.model_id[type=checkbox]:checked").each(function(){
				checked_ids.push($j(this).val());
			});
			
			if(checked_ids.length == 0)
			{
				alert(Alaxos.pleaseSelectAtLeastOneItem);
				
				return false;
			}
			
			/****/
			
			var form        = $j(this).parents("form")[0];
			var confirm_txt = $j(this).attr("data-confirm");
			
			$j(checked_ids).each(function(){
				
				$j(form).append("<input type=\"hidden\" name=\"checked_ids[]\" value=\"" + this + "\">");
				
			});
			
			if(typeof(confirm_txt) != "undefined" && confirm_txt.length > 0){
				if(confirm(confirm_txt)){
					form.submit();
				}
			}
			else{
				form.submit();
			}
		});
	}
	
	/********************************************************************
     * Ajax response treatment
     */
	
	function manage_ajax_error(data, selector_to_display)
	{	
		if(typeof(data.responseJSON) != "undefined" && typeof(data.responseJSON.errors) != "undefined")
		{
			var msg = build_message(data.responseJSON.errors);
			Alaxos.show_text(msg, "error", selector_to_display);
		}
		else if(typeof(data.responseJSON) != "undefined" && typeof(data.responseJSON.message) != "undefined")
		{
			Alaxos.show_text(data.responseJSON.message, "error", selector_to_display);
		}
		else if(typeof(data.errors) != "undefined")
		{
			var msg = build_message(data.errors);
			Alaxos.show_text(msg, "error", selector_to_display);
		}
		else
		{
			Alaxos.show_text("an error occured", "error", selector_to_display);
		}
	}
	
	function manage_ajax_success(data, selector_to_display)
	{	
		if(typeof(data.success) != "undefined")
		{
			var msg = build_message(data.success);
			Alaxos.show_text(msg, "success", selector_to_display);
		}
	}
	
	function build_message(data)
	{
		var msg = "";
		
		if(data.length > 1){
			msg += '<ul>';
		}
		
		$j(data).each(function(i, value){
			
			msg += (data.length > 1) ? "<li>" : "";
			msg += value;
			msg += (data.length > 1) ? "</li>" : "";
		});
		
		if(data.length > 1){
			msg += '</ul>';
		}
		
		return msg;
	}
	
	function show_text(text, css_class, selector_to_display)
	{
		if(typeof(selector_to_display) != "undefined" && selector_to_display != null)
		{
			if(typeof(css_class) != "undefined" && css_class != null){
				$j(selector_to_display).attr("class", css_class);
			}
			
			$j(selector_to_display).fadeOut(100, function(){
				$j(selector_to_display).html(text);
				$j(selector_to_display).fadeIn(100);
			});
		}
		else
		{
			var start_div = null;
			
			if(typeof(css_class) != "undefined" && css_class != null){
				start_div = '<div class="' + css_class + '">';
			}else{
				start_div = '<div>';	
			}
			
			var msg = start_div + text + "</div>";
			
			if($j("#alaxos_text_panel").length == 0){
				if($j("#content").length > 0){
					$j("#content").prepend('<div id="alaxos_text_panel"></div>');
				}
				else{
					$j("body").prepend('<div id="alaxos_text_panel"></div>');
				}
			}
			
			$j("#alaxos_text_panel").fadeOut(100, function(){
				$j(this).html(msg);
				$j(this).fadeIn(100);
			});
		}
	}
	
	/********************************************************************
     * Start auto scripts
     */
	function start(){
		Alaxos.register_rows_dblclick();
		Alaxos.register_select_all_checkboxes();
		Alaxos.register_select_row();
		Alaxos.register_action_all_btns();
	}
	
	/********************************************************************
     * Return Alaxos public variables and methods
     */
    return {
    	DEFAULT_DATE_FORMAT					:	DEFAULT_DATE_FORMAT,
    	pleaseSelectAtLeastOneItem			:	pleaseSelectAtLeastOneItem,
    	
//    	date_field							:	date_field,
    	get_date_format						:	get_date_format,
    	get_complete_date_object			:	get_complete_date_object,
    	explode_date_parts					:	explode_date_parts,
    	get_date_parts						:	get_date_parts,
    	get_date_part_value					:	get_date_part_value,
    	get_checked_day						:	get_checked_day,
    	get_checked_month					:	get_checked_month,
    	get_checked_year					:	get_checked_year,
    	get_complete_year					:	get_complete_year,
    	check_date_validity					:	check_date_validity,
    	is_bissextile						:	is_bissextile,
    	
    	time_field							:	time_field,
    	format_time							:	format_time,
    	
    	formatDateAndUpdateHiddenField		:	formatDateAndUpdateHiddenField,
    	updateDatetimeHiddenField			:	updateDatetimeHiddenField,
    	
    	number_field						:	number_field,
    	format_number						:	format_number,
    	
    	register_rows_dblclick				:	register_rows_dblclick,
    	register_select_all_checkboxes		:	register_select_all_checkboxes,
    	register_select_row					:	register_select_row,
    	register_action_all_btns			:	register_action_all_btns,
    	check_action_all_btns_state			:	check_action_all_btns_state,
    	
    	manage_ajax_error					:	manage_ajax_error,
    	manage_ajax_success					:	manage_ajax_success,
    	show_text							:	show_text,
    	
    	set_row_status						:	set_row_status,
    	
    	start								:	start
    }

})(jQuery);