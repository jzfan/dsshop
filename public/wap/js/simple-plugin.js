/*
 *此框架只依赖zepto.js
 */
(function($) {
    $.extend($, {
        sValid: function() {
            var $this = $.sValid;
            var sElement = $this.settings.sElement;
            for (var i = 0; i < sElement.length; i++) {
                var element = sElement[i];
                var sEl = $("#"+element).length >0 ? $("#"+element) : $("."+element);
                for(var j = 0;j<sEl.length;j++){
                     $this.check(element,sEl[j]);
                }
            }
            $this.callBackData();
            var cEid = $this.errorFiles.eId;
            var cEmsg = $this.errorFiles.eMsg;
            var cErules = $this.errorFiles.eRules;
            var isVlided = false;
            if (cEid.length > 0) {
                isVlided = false;
            } else {
                isVlided = true;
            }
            $this.settings.callback.apply(this, [cEid, cEmsg, cErules]);
            $this.destroyData();
            return isVlided;
        }
    });
    //sValid
    $.extend($.sValid, {
        defaults: {
            rules: {},
            messages: {},
            callback: function() {}
        },
        init: function(options) {
            //初始化控件参数
            var opt = $.extend({}, this.defaults, options);
            var rules = opt.rules;
            var messages = opt.messages;
            var sElement = [];
            $.map(rules, function(item, idx) {
                sElement.push(idx);
            });
            this.settings = {};
            this.settings["sElement"] = sElement;
            this.settings["sRules"] = rules;
            this.settings["sMessages"] = messages;
            this.settings['callback'] = opt.callback;
        },
        optional: function(element) {
            var val = this.elementValue(element);
            return !this.methods.required.call(this, val, element);
        },
        methods: {
            required: function(value, element) {
                if (element.nodeName.toLowerCase() === "select") {
                    var val = $(element).val();
                    return val && val.length > 0;
                }
                return $.trim(value).length > 0;
            },
            maxlength: function(value, element, param) {
                var length = $.trim(value).length;
                return this.optional(element) || length <= param;
            },
            minlength: function(value, element, param) {
                var length = $.trim(value).length;
                return this.optional(element) || length >= param;
            },
            //是否是合法数字（包括正数、负数）
            number: function(value, element, param) {
                return this.optional(element) || /^-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test(value);
            },
            digits: function(value, element, param) {
                return this.optional(element) || /^\d+$/.test(value);
            },
            email: function(value, element, param) {
                return this.optional(element) || /^[a-z0-9-]{1,30}@[a-z0-9-]{1,65}(\.[a-z0-9-]{1,65})*$/.test(value);
            },
            mobile: function(value, element, param) {
                return this.optional(element) || /^(1{1})+\d{10}$/.test(value);
            },
            equalTo: function( value, element, param ) {
                return this.optional(element) || value === $( param ).val();
            }
        },
        elementValue: function(element) {
            var type = $(element).attr("type");
            var value = $(element).val();
            if (typeof value === "string") {
                return value.replace(/\r/g, "");
            }
            return value;
        },
        rulesFormat: {
            required: true,
            email: true
        },
        errorFiles: {
            eId: [],
            eRules: {},
            eMsg: {}
        },
        check: function(element,mEl) {
            var settingsRules = [];
            var methods = this.methods;
            var rules = this.settings["sRules"];
            var mVal = this.elementValue.call(this, mEl);
            var mParam = [];
            var errorFiles = this.errorFiles;
            var errRules = [];
            //rules
            if (typeof rules[element] === "string") {
                if ($.inArray(rules[element], settingsRules) < 0) {
                    settingsRules.push(rules[element]);
                }
            } else {
                $.each(rules[element], function(idx, item) {
                    if ($.inArray(idx, settingsRules) < 0) {
                        settingsRules.push(idx);
                        if (idx == "maxlength" || idx == "minlength") {
                            mParam.push(parseInt(item));
                        } else {
                        	mParam.push(item);
                        }
                    }
                })
            }
            //checked
            for (var i = 0; i < settingsRules.length; i++) {
                if (!methods[settingsRules[i]].call(this, mVal, mEl, mParam[i])) {
                    errRules.push(settingsRules[i]);
                    errorFiles['eRules'][element] = errRules;
                    if ($.inArray(element, errorFiles['eId']) < 0) {
                        errorFiles['eId'].push(element);
                    }
                }
            }
        },
        callBackData: function() {
            var errorFiles = this.errorFiles;
            var errId = errorFiles.eId;
            var eMsg = errorFiles.eMsg;
            var eRules = errorFiles.eRules;
            var sMessages = this.settings.sMessages;
            for (var i = 0; i < errId.length; i++) {
                if (typeof sMessages[errId[i]] === "string") {
                    eMsg[errId[i] + "_" + eRules[errId[i]]] = sMessages[errId[i]];
                } else {
                    if ($.isArray(eRules[errId[i]])) {
                        for (var j = 0; j < eRules[errId[i]].length; j++) {
                            eMsg[errId[i] + "_" + eRules[errId[i]][j]] = sMessages[errId[i]][eRules[errId[i]][j]]
                        }
                    }
                }
            }
        },
        destroyData: function() {
            this.errorFiles = {
                eId: [],
                eRules: {},
                eMsg: {}
            };
        }
    });
})(Zepto);