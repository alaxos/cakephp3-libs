
(function( $ ) {

    $.widget( "alaxos.datetimewidget", $.alaxos.datewidget,{

        options: {
            time : {
                with_seconds : false,
                auto_complete: true,
                auto_correct_invalid_values : false
            },
            i18n : {
                en : {
                    hour_is_invalid   : "hour is invalid",
                    minute_is_invalid : "minutes are invalid"
                },
                fr : {
                    hour_is_invalid   : "l'heure est invalide",
                    minute_is_invalid : "les minutes sont invalides"
                }
            }
        },

        _create: function () {

            this._super();

            this._completeHtml();
        },

        _completeHtml : function () {
            var time = this.options["time"]["value"];

            /*
            <div class="input datetime">
                <div class="alaxos-datetime">

                    <div class="time alaxos-datepart">
                        <div class="input-group date alaxos-date" id="collectionBegin-date-container">
                            <input type="text" name="collectionBegin__date__" id="collectionBegin-date" upper_datepicker_id="collectionEnd-date" upper_datepicker_name="collectionEnd" class="form-control inputDate">
                            <span class="input-group-addon" id="collectionBegin-date-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                        </div>
                    </div>

                    <div class="time alaxos-timepart">
                        <span class="glyphicon glyphicon-time time-icon"></span>
                        <input type="text" name="collectionBegin__time__" id="collectionBegin-time" class="form-control inputTime">
                    </div>

                </div>
                <input type="hidden" name="collectionBegin" id="collectionBegin-hidden" value="">
            </div>
            */

            var dateFieldId = this.element.attr("id");
            var dateFieldName = this.element.attr("name");
            var timeFieldId = dateFieldId.replace(/\-date/, "-time");
            var timeFieldName = dateFieldName.replace(/__date__/, "__time__");
            var hiddenFieldId = dateFieldId.replace(/\-date/, "-hidden");
            var hiddenFieldName = dateFieldName.replace(/__date__/, "");

            this.options["time_field"] = $('<input type="text" name="' + timeFieldName + '" id="' + timeFieldId + '" class="form-control inputTime">');
            this.options["time_zone"] = $('<div class="time alaxos-timepart"><span class="glyphicon glyphicon-time time-icon"></span></div>');
            this.options["hidden_field"] = $('<input type="hidden" name="' + hiddenFieldName + '" id="' + hiddenFieldId + '" value="">');

            this.options["time_field"].val(time);

            this.options["time_zone"].append(this.options["time_field"]);

            var datetime_div = this.element.closest(".alaxos-datetime");
            datetime_div.append(this.options["time_zone"]);
            datetime_div.append(this.options["hidden_field"]);

            this._updateHiddenField();

            this.options["time_field"].blur($.proxy(this._formatTime, this));
        },

        _formatTime : function () {
            this._clearError();

            var time_str = this.options["time_field"].val();

            if (time_str.length > 0) {
                try {
                    var completedTime = this._getCompleteTime(time_str)
                    this.options["time_field"].val(completedTime);

                    this._updateHiddenField();

                } catch (err) {
                    this._displayError(err);
                }
            } else {
                this._manageUpperDatepicker();
            }
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
            } else {
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
            } else {
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
                } else {
                    sec = "00";
                }

                newValue += ":" + sec;
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

        _updateHiddenField : function () {
            var dateVal = this.element.val();
            var timeVal = this.options["time_field"].val();

            if (dateVal.length > 0 && timeVal.length > 0) {
                this.options["hidden_field"].val(dateVal + " " + timeVal);
            } else {
                this.options["hidden_field"].val("");
            }
        }
    });

}( jQuery ));