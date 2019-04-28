
(function( $ ) {

    $.widget( "alaxos.datetimewidget", $.alaxos.datewidget,{

        options: {
            time : {
                with_seconds : false,
                auto_complete: true,
                auto_correct_invalid_values : false,
                show_placeholder : true
            },
            i18n : {
                en : {
                    hour_is_invalid   : "hour is invalid",
                    minute_is_invalid : "minutes are invalid",
                    time_is_incomplete : "time is incomplete"
                },
                fr : {
                    hour_is_invalid   : "l'heure est invalide",
                    minute_is_invalid : "les minutes sont invalides",
                    time_is_incomplete : "l'heure est incomplète"
                }
            }
        },

        _create: function () {

            this._super();

            // listening to clearDate event is useful to update hidden field when lower datepicker set the current one's value to null
            this.element.datepicker().on("clearDate", $.proxy(this._datePickerClearDate, this));
            
            this._completeHtml();
        },

        _completeHtml : function () {
            var time = this.options["time"]["value"];

            var dateFieldId = this.element.attr("id");
            var timeFieldId = dateFieldId.replace(/-date$/, "-time");
            var hiddenFieldId = dateFieldId.replace(/-date$/, "-hidden");
            
            this.options["time_field"] = $("#" + timeFieldId);
            this.options["hidden_field"] = $("#" + hiddenFieldId);
            
            //move error zone outside of date div part
            var datetime_div = this.element.closest(".alaxos-datetime");
            datetime_div.append(this.options["error_zone"]);
            
            this.options["time_field"].blur($.proxy(this._formatTime, this));
            this.options["time_field"].keypress($.proxy(this._keyPressedInTimeField, this));
        },

        _formatTime : function () {
            this._clearError();

            var time_str = this.options["time_field"].val();

            if (time_str.length > 0) {
                try {
                    var completedTime = this._getCompleteTime(time_str);
                    this.options["time_field"].val(completedTime);
                } catch (err) {
                    this._displayError(err);
                }
            } else {
                this._manageUpperDatepicker();
            }

            this._updateHiddenField();
        },

        _getCompleteTime : function (timeStr) {

            if (timeStr.length == 0) {
                return;
            } else if (timeStr.length == 3) {
                timeStr = timeStr.substring(0, 2) + ":" + timeStr.substring(2, 3);
            } else if (timeStr.length == 4) {
                timeStr = timeStr.substring(0, 2) + ":" + timeStr.substring(2, 4);
            } else if (timeStr.length == 6) {
                timeStr = timeStr.substring(0, 2) + ":" + timeStr.substring(2, 4) + ":" + timeStr.substring(4, 6);
            }

            var numbers = "0123456789";

            var hour = "";
            var min  = "";
            var sec  = "";
            var separators_found = 0;
            var last_is_separator = false;
            for (var i = 0; i < timeStr.length; i++) {
                if (numbers.indexOf(timeStr.charAt(i)) != -1) {
                    last_is_separator = false;

                    if (separators_found == 0) {
                        hour += timeStr.charAt(i);
                    } else if (separators_found == 1) {
                        min += timeStr.charAt(i);
                    } else if (separators_found == 2) {
                        sec += timeStr.charAt(i);
                    }
                } else {
                    if (last_is_separator == false) {
                        separators_found++;
                    }

                    last_is_separator = true;
                }
            }

            var newValue = "";

            if (hour.length > 0) {
                hour         = parseInt(hour, 10);
                var hour_str = hour + "";

                if (hour > 23) {
                    if (this.options["time"]["auto_correct_invalid_values"]) {
                        hour = hour % 24 + "";
                        hour_str = hour + "";
                    } else {
                        throw "hour_is_invalid";
                    }
                }

                if (hour < 10 && hour_str.length < 2) {
                    hour = "0" + hour;
                }
            } else if (this.options["time"]["auto_complete"]) {
                hour = "00";
            }

            newValue += hour + ":";

            if (min.length > 0) {
                min         = parseInt(min, 10);
                var min_str = min + "";

                if (min > 59) {
                    if (this.options["time"]["auto_correct_invalid_values"]) {
                        min = min % 60;
                        min_str = min + "";
                    } else {
                        throw "minute_is_invalid";
                    }
                }

                if (min < 10 && min_str.length < 2) {
                    min = "0" + min;
                }
            } else if (this.options["time"]["auto_complete"]) {
                min = "00";
            }

            newValue += min;

            if (this.options["time"]["with_seconds"]) {
                if (sec.length > 0) {
                    if (sec > 60) {
                        sec = sec % 60 + "";
                    }

                    if (sec < 10 && sec.length < 2) {
                        sec = "0" + sec;
                    }
                } else if (this.options["time"]["auto_complete"]) {
                    sec = "00";
                }

                newValue += ":" + sec;
            }


            if (this.options["time"]["with_seconds"] && newValue.length < 8) {
                throw "time_is_incomplete";
            } else if (!this.options["time"]["with_seconds"] && newValue.length < 5) {
                throw "time_is_incomplete";
            }

            return newValue;
        },

        _formatDate: function () {
            this._super();

            var me = this;
            setTimeout(function() {
                me._updateHiddenField();
            }, 50);
        },

        _datePickerChangeDate : function () {
            this._super();
//            console.log(this.element.attr("id") + "._datePickerChangeDate()");
            this._updateHiddenField();
        },
        
        _datePickerClearDate : function () {
//            console.log(this.element.attr("id") + "._datePickerClearDate()");
            
            var me = this;
            setTimeout(function() {
//                console.log(me.element.attr("id") + "._datePickerClearDate()   timeout");
                me._updateHiddenField();
            }, 50);
            
        },
        
        _updateHiddenField : function () {
//            console.log(this.element.attr("id") + "._updateHiddenField()");
            
            var dateVal = this.element.val();
            var timeVal = this.options["time_field"].val();

            var timeValLength = this.options["time"]["with_seconds"] ? 8 : 5;

            if (dateVal.length == this.options["datepicker"]["format"].length && timeVal.length == timeValLength) {
                this.options["hidden_field"].val(dateVal + " " + timeVal);
            } else {
                this.options["hidden_field"].val("");
            }
        },

        _keyPressedInTimeField : function (e) {
            var time_str = this.options["time_field"].val();

            if (time_str != null && time_str.length > 0) {
                if (e.which == 13) {
                    this._clearError();
                    try {
                        var completedTime = this._getCompleteTime(time_str);
                        this.options["time_field"].val(completedTime);
                        
                        if (time_str != completedTime) {
                            e.preventDefault();
                        }
                    } catch (err) {
                        this._displayError(err);
                        e.preventDefault();
                    }
                    this._updateHiddenField();
                }
            }
        }
    });

}( jQuery ));