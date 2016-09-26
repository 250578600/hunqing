/*!
 * jQuery Cookie Plugin v1.4.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2006, 2014 Klaus Hartl
 * Released under the MIT license
 */
(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD (Register as an anonymous module)
		define(['jquery'], factory);
	} else if (typeof exports === 'object') {
		// Node/CommonJS
		module.exports = factory(require('jquery'));
	} else {
		// Browser globals
		factory(jQuery);
	}
}(function ($) {

	var pluses = /\+/g;

	function encode(s) {
		return config.raw ? s : encodeURIComponent(s);
	}

	function decode(s) {
		return config.raw ? s : decodeURIComponent(s);
	}

	function stringifyCookieValue(value) {
		return encode(config.json ? JSON.stringify(value) : String(value));
	}

	function parseCookieValue(s) {
		if (s.indexOf('"') === 0) {
			// This is a quoted cookie as according to RFC2068, unescape...
			s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
		}

		try {
			// Replace server-side written pluses with spaces.
			// If we can't decode the cookie, ignore it, it's unusable.
			// If we can't parse the cookie, ignore it, it's unusable.
			s = decodeURIComponent(s.replace(pluses, ' '));
			return config.json ? JSON.parse(s) : s;
		} catch(e) {}
	}

	function read(s, converter) {
		var value = config.raw ? s : parseCookieValue(s);
		return $.isFunction(converter) ? converter(value) : value;
	}

	var config = $.cookie = function (key, value, options) {

		// Write

		if (arguments.length > 1 && !$.isFunction(value)) {
			options = $.extend({}, config.defaults, options);

			if (typeof options.expires === 'number') {
				var days = options.expires, t = options.expires = new Date();
				t.setMilliseconds(t.getMilliseconds() + days * 864e+5);
			}

			return (document.cookie = [
				encode(key), '=', stringifyCookieValue(value),
				options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
				options.path    ? '; path=' + options.path : '',
				options.domain  ? '; domain=' + options.domain : '',
				options.secure  ? '; secure' : ''
			].join(''));
		}

		// Read

		var result = key ? undefined : {},
			// To prevent the for loop in the first place assign an empty array
			// in case there are no cookies at all. Also prevents odd result when
			// calling $.cookie().
			cookies = document.cookie ? document.cookie.split('; ') : [],
			i = 0,
			l = cookies.length;

		for (; i < l; i++) {
			var parts = cookies[i].split('='),
				name = decode(parts.shift()),
				cookie = parts.join('=');

			if (key === name) {
				// If second argument (value) is a function it's a converter...
				result = read(cookie, value);
				break;
			}

			// Prevent storing a cookie that we couldn't decode.
			if (!key && (cookie = read(cookie)) !== undefined) {
				result[name] = cookie;
			}
		}

		return result;
	};

	config.defaults = {};

	$.removeCookie = function (key, options) {
		// Must not alter options, thus extending a fresh object...
		$.cookie(key, '', $.extend({}, options, { expires: -1 }));
		return !$.cookie(key);
	};

}));




// input_file_str: file input 的jq 选择 字符串
// url: php  处理文件
// input_upload_str: 上传到后台的 的jq 选择 字符串
// img: 前台显示的图片  格式：节点
function install_upload(input_file_str,url,input_upload_str,img){
	if($("#upload_f").length==0){
		f=document.createElement("iframe");
		f.style.display="none";
		f.style.width="0px";
		f.id="upload_f";
		$("body").append(f);
	}
	
	function check(){
		name=$(input_file_str).attr("name");
		src=$.cookie(name);
		if('0'==src)setTimeout(check,500);
		else{
			img.src=src;
			$(input_upload_str).val(src);
		}
	}
	function upload_fun(){
		name=$(input_file_str).attr("name");
		file=$(input_file_str);
		f=$("#upload_f");
		d=f[0].contentDocument;
		d.write("<form action='"+url+"' id='form' method='post' enctype='multipart/form-data'></form>");
		
		form=$(d).find('form');
		bc=file.parent();
		bc_html=bc.html();
		form.append(file[0]);
		bc.html(bc_html);
		$(input_file_str).change(upload_fun);
		
		document.cookie=name+"=0;path=/";
		form.submit();
		setTimeout(check,500);
	};
	$(input_file_str).change(upload_fun);
	console.log($(input_file_str));
}









// input_file_str: file input 的jq 选择 字符串
// url: php  处理文件
// model: 模板 格式：节点
// container: 容器 格式：节点
function install_upload2(input_file_str,url,container,model){
	function del(e){
		$(e).parent().remove();
	}
	$(document).ready(function(){
		container.find("img").click(function(){del(this);});
	});
	
	if($("#upload_f").length==0){
		f=document.createElement("iframe");
		f.style.display="none";
		f.style.width="0px";
		f.id="upload_f";
		$("body").append(f);
	}
	
	function check(){
		name=$(input_file_str).attr("name");
		name=name.substr(0,name.length-2);
		src=$.cookie(name);
		if('0'==src)setTimeout(check,500);
		else{
			src=eval(src);
			for(i=0;i<src.length;i++){
					html=model.clone();
					html.find("img")[0].src=src[i];
					html.find("img").click(function(){del(this);});
					html.find("input[type=hidden]")[0].value=src[i];
					container.append(html);
			}
		}
	}
	function upload_fun(){
		file=$(input_file_str);
		name=file.attr("name");
		name=name.substr(0,name.length-2);
		f=$("#upload_f");
		d=f[0].contentDocument;
		d.write("<form action='"+url+"' id='form' method='post' enctype='multipart/form-data'></form>");
		
		form=$(d).find('form');
		bc=file.parent();
		bc_html=bc.html();
		form.append(file[0]);
		bc.html(bc_html);
		$(input_file_str).change(upload_fun);
		
		document.cookie=name+"=0;path=/";
		form.submit();
		setTimeout(check,500);
	};
	$(input_file_str).change(upload_fun);
}