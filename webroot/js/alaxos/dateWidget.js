
(function( $ ) {

    $.widget( "alaxos.datewidget", {

        options: {
            auto_complete_date: true,
            auto_correct_invalid_values : false,
            datepicker: {
                format: "mm/dd/yyyy",
                language: "en",
                forceParse : false,
                autoclose : true,
                todayHighlight: true,
                showOnFocus : false
            },
            show_placeholder : true,
            upper_datepicker_selector : null,
            i18n : {
                en : {
                    day_is_invalid   : "day is invalid",
                    month_is_invalid : "month is invalid",
                    year_is_invalid  : "year is invalid",
                    day_is_empty     : "day is empty",
                    month_is_empty   : "month is empty",
                    year_is_empty    : "year is empty"
                },
                fr : {
                    day_is_invalid   : "le jour est invalide",
                    month_is_invalid : "le mois est invalide",
                    year_is_invalid  : "l'année est invalide",
                    day_is_empty     : "le jour est absent",
                    month_is_empty   : "le mois est absent",
                    year_is_empty    : "l'année est absente"
                }
            }
        },

        date_on_blur_timeout : null,

        _create: function () {

            this.options["internal_date_format"] = this._getInternalDateFormnat(this.options["datepicker"]["format"])

            this._buildHtml();

            this.element.datepicker(this.options["datepicker"]);
            this.element.datepicker().on("changeDate", $.proxy(this._datePickerChangeDate, this));

            this.element.blur($.proxy(this._formatDate, this));
            this.element.keypress($.proxy(this._keyPressed, this));
            this.options["icon"].click($.proxy(this._showDatePicker, this));

            // Setting the lower limit too early seems to brake the date format -> wait one second
            var me = this;
            var initStartDate = setTimeout(function () {
                me._manageUpperDatepicker();
            }, 1000);
        },

        _buildHtml : function () {
            this.element.addClass("input-date");

            if (this.options["show_placeholder"]) {
                this.element.prop("placeholder", this.options["datepicker"]["format"]);
            }

            this.options["error_zone"] = $('<div class="error"></div>');
            this.element.after(this.options["error_zone"]);

            this.element.wrap('<div class="input-group date alaxos-date"></div>');

            this.options["icon"] = $('<span class="input-group-addon" id="date-field-group-addon"><i class="glyphicon glyphicon-th"></i></span>');
            this.element.after(this.options["icon"]);
        },

        _showDatePicker : function () {
            this.element.datepicker("show");
        },

        _datePickerChangeDate : function () {
            clearTimeout(this.date_on_blur_timeout);
            this._clearError();
            this._manageUpperDatepicker();
        },

        _manageUpperDatepicker: function () {
            if (this.options["upper_datepicker_selector"] != null) {
                $(this.options["upper_datepicker_selector"]).datepicker("setStartDate", this.element.datepicker("getDate"));
                var upperDate = $(this.options["upper_datepicker_selector"]).datepicker("getDate");
                if (upperDate == null) {
                    $(this.options["upper_datepicker_selector"]).datepicker("update", "");
                }
            }
        },

        _getInternalDateFormnat: function (datepickerFormat) {
            var internalDateFormat = datepickerFormat.replace(/dd/g, 'd');
            internalDateFormat = internalDateFormat.replace(/mm/g, 'm');
            internalDateFormat = internalDateFormat.replace(/yyyy/g, 'y').replace(/yyy/g, 'y').replace(/yy/g, 'y');
            return internalDateFormat;
        },

        _setOption: function (key, value) {
            this.options[key] = value;

            if (key == "format") {
                this.element.datepicker('option', 'format', value);
            }

            this._formatDate();
        },

        _keyPressed: function (e) {

            var date_str = this.element.val();

            if (date_str != null && date_str.length > 0) {
                if (e.which == 13) {
                    this._clearError();

                    var completedDate = this._getCompleteDateObject(date_str)
                    this.element.datepicker("setDate", completedDate);

                    var newValue = this.element.val();
                    if (newValue != date_str) {
                        e.preventDefault();
                    }
                }
            }
        },

        _formatDate: function () {
            this._clearError();

            var date_str = this.element.val();

            if (date_str.length > 0) {
                try {
                    var completedDate = this._getCompleteDateObject(date_str)
                    var me = this;

                    /*
                     * wait a bit before setting the new value --> allow the datepicker to...
                     */
                    this.date_on_blur_timeout = setTimeout(function () {
                        if ($(".datepicker:visible").length == 0) {
                            me.element.datepicker("setDate", completedDate);
                        }
                    }, 30);
                } catch (err) {
                    this._displayError(err);
                }
            }
        },

        _displayError: function (error) {
            this.options["error_zone"].html(this._getErrorMessage(error));
        },

        _getErrorMessage: function (error) {
            var language = this.options["datepicker"]["language"];
            if (typeof (this.options["i18n"][language]) != "undefined" && typeof (this.options["i18n"][language][error]) != "undefined") {
                return this.options["i18n"][language][error];
            } else {
                return error;
            }
        },

        _clearError : function () {
            this.options["error_zone"].html("");
        },

        _getCompleteDateObject: function (date_str) {
            var exploded_date_parts  = this._explodeDateParts(date_str);
            var completed_date_parts = this._getDateParts(exploded_date_parts)

            var year  = completed_date_parts["year"];
            var month = completed_date_parts["month"] -1; //month is zero based in Date object
            var day   = completed_date_parts["day"];

            return new Date(year, month, day);
        },

        _explodeDateParts: function (date_str) {
            var separator1 = '';
            var separator2 = '';
            var value1 = '';
            var value2 = '';
            var value3 = '';

            var previous_value = '';
            var typed_index = 0;
            var i;
            for (i = 0; i < date_str.length; i++) {
                current_char = date_str.charAt(i);

                if (i == 0 || (previous_value == ' ' && !isNaN(current_char)) || (current_char == ' ' && !isNaN(previous_value)) || (isNaN(current_char) && !isNaN(previous_value)) || (!isNaN(current_char) && isNaN(previous_value))) {
                    //change from number to separator or from separator to number
                    typed_index++;

                    //manage the case of a value starting with a separator
                    if (i == 0 && isNaN(current_char)) {
                        typed_index = 2;
                    }
                }

                switch (typed_index) {
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
                value1      :   value1,
                separator1  :   separator1,
                value2      :   value2,
                separator2  :   separator2,
                value3      :   value3
            }
        },
        
        _getDateParts: function (exploded_date_parts) {
            
            var date_part1 = this.options["internal_date_format"].substring(0, 1);
            var separator1 = this.options["internal_date_format"].substring(1, 2);
            var date_part2 = this.options["internal_date_format"].substring(2, 3);
            var separator2 = this.options["internal_date_format"].substring(3, 4);
            var date_part3 = this.options["internal_date_format"].substring(4, 5);

            var date_part_value1 = this._getDatePartValue(exploded_date_parts["value1"], date_part1);
            var date_part_value2 = this._getDatePartValue(exploded_date_parts["value2"], date_part2);
            var date_part_value3 = this._getDatePartValue(exploded_date_parts["value3"], date_part3);

            var day = null;
            var month = null;
            var year = null;
            switch (date_part1) {
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
            switch (date_part2) {
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
            switch (date_part3) {
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
                year    :   year,
                month   :   month,
                day     :   day
            }
        },
        
        _getDatePartValue: function (value, date_part) {
            switch (date_part) {
                case 'd':
                    return this._getCheckedDay(value);
                    break;
                case 'm':
                    return this._getCheckedMonth(value);
                    break;
                case 'y':
                    return this._getCheckedYear(value);
                    break;
            }
        },

        _getCheckedDay: function (value) {
            var day = null;

            if (value == null || value.length == 0) {
                if (this.options["auto_complete_date"]) {
                    var date = new Date();
                    day = date.getDate();
                } else {
                    throw "day_is_empty";
                }
            } else {
                if (isNaN(value) || value < 1 || value > 31) {
                    if (this.options["auto_correct_invalid_values"]) {
                        var date = new Date();
                        day = date.getDate();
                    } else {
                        throw "day_is_invalid";
                    }
                } else {
                    day = value;
                }
            }

            return day;
        },

        _getCheckedMonth: function (value) {
            var month = null;

            if (value == null || value.length == 0) {
                if (this.options["auto_complete_date"]) {
                    var date = new Date();
                    month = date.getMonth();
                    month = month + 1;
                } else {
                    throw "month_is_empty";
                }
            } else {
                if (isNaN(value) || value < 1 || value > 12) {
                    if (this.options["auto_correct_invalid_values"]) {
                        var date = new Date();
                        month = date.getMonth();
                        month = month + 1;
                    } else {
                        throw "month_is_invalid";
                    }
                } else {
                    month = value;
                }
            }

            return month;
        },

        _getCheckedYear: function (value) {
            var year = null;

            if (value == null || value.length == 0) {
                if (this.options["auto_complete_date"]) {
                    var date = new Date();
                    year = date.getFullYear();
                } else {
                    throw "year_is_empty";
                }
            } else {
                if (isNaN(value)) {
                    if (this.options["auto_correct_invalid_values"]) {
                        var date = new Date();
                        year = date.getFullYear();
                    } else {
                        throw "year_is_invalid";
                    }
                } else {
                    var complete_year = this._getCompleteYear(value);

                    var current_date = new Date();
                    var current_year = current_date.getFullYear();
                    var year_diff = complete_year - current_year;

                    if (year_diff > 15) {
                        year = this._getCompleteYear(complete_year - 100);
                    } else {
                        year = complete_year;
                    }
                }
            }

            return year;
        },

        _getCompleteYear: function (year) {
            var date = new Date(year, 1, 1);

            var full_date = date.getFullYear();

            if (year.length < 4 && full_date < 2000) {
                full_date += 100;
            }

            return full_date;
        }
    });

}( jQuery ));
