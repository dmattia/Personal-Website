(function($) {

    var T = $.fn.tn3.TransitionInspector = function(c) {
	this.$c = c;
	this.$c.css("width", "100%")
	       .css("height", "640px");
	//this.$c.text("Click on the image to preview");
	this.$out = this.$c.append('<div class="out"></div>').find(":last");
	this.$conc = this.$c.append("<div></div>").find(":last");
	this.$concc = this.$conc.append("<div></div>").find(":last");
	this.$conc.css("width", "240px")
		  .css("float", "left")
		  .css("margin", "20px");
	//this.$concc.css("position", "relative").width(590);

	this.$imgc = this.$c.append("<div></div>").find(":last");
	this.$imgc.width(620).height(378);
	this.img = new $.fn.tn3.Imager(this.$imgc, [
	    {
		img:tn3.pluginPath + "images/2.jpg"
	    },{
		img:tn3.pluginPath +"images/5.jpg"
	    }
	], {});
	this.img.show(0);
	var ins = this;
	this.$imgc.bind("img_click", function(e) {
	    ins.img.show("next");
	});
	this.$imgc.css("float", "left")
		  .css("margin", "20px");

	this.createSelect("type", $.fn.tn3.Transitions.defined, "fade", true);
	this.$preset = $('#tn3_transition_presets');
	var $typeCombo = $('select[name|="tn3_type"]');
	this.$preset.change(function(e) {
	    var cur = tn3.transitions[e.target.value];
	    $typeCombo.empty();
	    if (e.target.value == "default") {
		$("#tn3_transition_presets_del").hide();
		$typeCombo.append($('<option></option>').html("none"));
		$typeCombo.append($('<option></option>').html("fade"));
		$typeCombo.append($('<option></option>').html("slide"));
	    } else {
		$("#tn3_transition_presets_del").show();
		$.each($.fn.tn3.Transitions.defined, function(val, text) {
		    $typeCombo.append($('<option></option>').html(text));
		});
	    }
	    ins.$concc.empty();
	    ins.tobj = {type:cur.type};
	    $typeCombo.val(cur.type);
	    ins[cur.type + "Extra"](cur);
	});
	this.$preset.change();

    };
    T.prototype = {
	$c:null,
	$imgc:null,
	img:null,
	$conc:null,
	$concc:null,
	tobj:{},
	createUI: function(typ) 
	{
	    this.$concc.empty();
	    this.tobj = {};
	    var po = $.fn.tn3.Transitions.prototype[typ + "Config"];
	    this[typ + "Extra"](po);
	    return po;
	},
	createContainer: function(lab)
	{
	    var $mc = this.$concc.append("<div><fieldset></fieldset></div>").find(":last").css("margin", "6px");
	    $mc.append('<legend>' + lab + '</legend>');
	    $mc.find("legend").css("padding-bottom", "3px")
			      .css("font-style", "italic");
	    return $mc.append("<div></div>").find(":last");
	},
	createSlider: function(lab, min, max, def) 
	{
	    var $mc = this.createContainer(lab);
	    //$mc.css("padding", "10px");
	    var $val = $('<div class="value">' + def + '</div>');
	    $val.css("padding-top", "3px")
	    $val.appendTo($mc.parent());
	    var ins = this;
	    $mc.slider({
		min: min,
		max: max,
		value: def,
		slide: function(e, ui) {
		    $val.text(ui.value);
		    ins.setValue(lab, ui.value);
		}
	    });
	    this.setValue(lab, def);
	},
	createSelect: function(lab, obj, def, outc)
	{
	    if (outc) var $mc = this.$conc.prepend('<select class="combo" name="tn3_'+lab+'"></select>').find(":first");
		else var $mc = this.createContainer(lab).append('<select class="combo"></select>').find(":last");
	    $.each(obj, function(val, text) {
		$mc.append($('<option></option>').val($.isArray(obj)? text:val).html(text));
	    });
	    $mc.find("option[value='" + def + "']").attr("selected", "selected");
	    var ins = this;
	    $mc.change(function(e) {
		var v = $(this).find("option:selected").val();
		ins.setValue(lab, v);
	    });
	    this.setValue(lab, def);
	    return $mc;
	},
	createCheck: function(lab, def)
	{
	    var $mc = this.createContainer(lab).append('<input type="checkbox"></input>').find(":last");
	    var ins = this;
	    $mc.change(function(e) {
		ins.setValue(lab, e.target.checked);
	    });
	    $mc.attr("checked", def);
	    this.setValue(lab, def);
	},
	setValue: function(p, v)
	{
	    if (p == "type") {
		this.createUI(v);		
	    }
	    this.tobj[p] = v;
	    this.img.ts.ts = [this.tobj];
	    var $pars = $('input[name|="tn3_transition_params"]');
	    $pars.val(escape(JSON.stringify(this.tobj)));
	},
	getQuotes: function(val) 
	{
	    if (typeof(val) == "string") return '"' + val + '"';
	    return val;
	},
	noneExtra: function(p)
	{
	},
	fadeExtra: function(p) 
	{
	    this.createSlider("duration", 0, 5000, p.duration);
	    this.createSelect("easing", ease, p.easing);
	},
	slideExtra: function(p) 
	{
	    this.createSlider("duration", 0, 5000, p.duration);
	    this.createSelect("direction", ["left", "right", "top", "bottom", "auto"], p.direction);
	    this.createSelect("easing", ease, p.easing);
	},
	blindsExtra: function(p) 
	{
	    this.createSlider("duration", 0, 5000, p.duration);
	    this.createSelect("direction", ["horizontal", "vertical"], p.direction);
	    this.createSelect("easing", ease, p.easing);
	    this.createSlider("parts", 1, 50, p.parts);
	    this.createSlider("partDuration", 0, 5000, p.partDuration);
	    this.createSelect("partEasing", ease, p.partEasing);
	    this.createSelect("method", ["fade", "scale", "slide"], p.method);
	    this.createSelect("partDirection", ["left", "right", "top", "bottom", "auto"], p.partDirection);
	    this.createCheck("cross", p.cross);
	},
	gridExtra: function(p) 
	{
	    this.createSlider("duration", 0, 5000, p.duration);
	    this.createSelect("easing", ease, p.easing);
	    this.createSlider("gridX", 1, 50, p.gridX);
	    this.createSlider("gridY", 1, 50, p.gridY);
	    this.createSelect("sort", ["flat", "diagonal", "circle", "random"], p.sort);
	    this.createCheck("sortReverse", p.sortReverse);
	    this.createSelect("diagonalStart", {
		tr: "top right",
		tl: "top left",
		br: "bottom right",
		bl: "bottom left"
	    }, p.diagonalStart);
	    this.createSelect("method", ["fade", "scale"], p.method);
	    this.createSlider("partDuration", 0, 5000, p.partDuration);
	    this.createSelect("partEasing", ease, p.partEasing);
	    this.createSelect("partDirection", ["left", "top"], p.partDirection);
	},
	gradientSwapExtra: function(p) 
	{
	    this.createSlider("duration", 0, 5000, p.duration);
	    this.createSelect("easing", ease, p.easing);
	    this.createSelect("direction", ["left", "right", "auto"], p.direction);
	    this.createSlider("spread", 1, 150, p.spread);
	    this.createCheck("radial", p.radial);
	}
    }
    var ease = {
	swing: "swing",
	linear: "linear",
	easeInQuad: "quadIn",
	easeOutQuad: "quadOut",
	easeInOutQuad: "quadInOut",
	easeInCubic: "cubicIn",
	easeOutCubic: "cubicOut",
	easeInOutCubic: "cubicInOut",
	easeInQuart: "quartIn",
	easeOutQuart: "quartOut",
	easeInOutQuart: "quartInOut",
	easeInQuint: "quintIn",
	easeOutQuint: "quintOut",
	easeInOutQuint: "quintInOut",
	easeInSine: "sineIn",
	easeOutSine: "sineOut",
	easeInOutSine: "sineInOut",
	easeInExpo: "expoIn",
	easeOutExpo: "expoOut",
	easeInOutExpo: "expoInOut",
	easeInCirc: "circIn",
	easeOutCirc: "circOut",
	easeInOutCirc: "circInOut",
	easeInElastic: "elasticIn",
	easeOutElastic: "elasticOut",
	easeInOutElastic: "elasticInOut",
	easeInBack: "backIn",
	easeOutBack: "backOut",
	easeInOutBack: "backInOut",
	easeInBounce: "bounceIn",
	easeOutBounce: "bounceOut",
	easeInOutBounce: "bounceInOut" 
    };

    $(document).ready(function() {
	var tins = new $.fn.tn3.TransitionInspector($("#tn3-transitions"));
    });
    
})(jQuery);
