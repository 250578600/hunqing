var ng={
	edition:'1.0',
	alert:function(direction,html,time,callback){ //提示框
		direction = direction!='top'?
					(direction!='bottom'?'top':direction):direction;
		callback = typeof callback=="function" ? callback : function(){};
		$('body').append('<div class="ng-alert-'+direction+'">'+html+'</div>');
		var _obj=$('.ng-alert-'+direction+':last');
		_obj.css(direction,parseInt(_obj.css(direction))+_obj.height());
		setTimeout(function(){
			_obj.css(direction,parseInt(_obj.css(direction))-_obj.height());
			setTimeout(function(){
				_obj.remove();
				callback();
			},500);
		},time);
		return ng;
		
		// js
		
	},
	layout:function(obj){
		obj = document.getElementsByClassName(obj);
		var layout, target;
		for(var i=0;i<obj.length;i++){
			// alert(obj[i].getAttribute('layout')); !null
			target = obj[i];
			layout = eval('('+obj[i].getAttribute('layout')+')');
			layout.flex && (
				target.parentNode.className += ' ng-flex',
				target.style.webkitBoxFlex = layout.flex,
				target.style.msFlex = layout.flex,
				target.style.flex = layout.flex
			);
			layout.clamp && (
				layout.clamp==1 ? target.className += ' ng-ellipsis'
								: target.className += ' ng-ellipsis-clamp',
				target.style.webkitLineClamp = layout.clamp
			);
		}
	},
	_layout:function(obj){ //布局
		obj=$(obj);
		var leftReg = RegExp('<','g'),
			rightReg = RegExp('>','g');
		var _$={
			obj:undefined,
			clear:function(){ //清空layout数据
				this.obj.removeAttr('_layout');
				return this;
			},
			layout:function(obj){
				layout(obj);
				return this;
			}
		};
		function calculation(str,_this){ //计算
			var _attr = {
				't.w':parseInt(_this.width()),
				't.h':parseInt(_this.height()),
				'p.w':parseInt(_this.parent().width()),
				'p.h':parseInt(_this.parent().height()),
				'this':'$(_this)',
				'100%':parseInt(_this.parent().width()) //默认指width
			};
			for(var i in _attr){
				var reg = new RegExp(i,'g');
				str=str.replace(reg,_attr[i]);
			}
			str=eval('('+str+')');
			return str;
		}
		function attr(str){ //属性
			var _attr = {
				'w':'width',
				'h':'height'
			};
			var _str=str;
			for(var i in _attr){
				if(str==_attr[i]) break;
				var reg = new RegExp(i,'g');
				_str=str.replace(reg,_attr[i]);
				if(_str!=str) break;
			}
			return _str;
		}
		for(var i=0;i<obj.length;i++){
			try{
				var str = obj.eq(i).attr('_layout').replace(leftReg,'"').replace(rightReg,'"');
				var json=eval('({'+str+'})');
				for(var j in json){
					obj.eq(i).css( attr(j), calculation(json[j],obj.eq(i)) );
				}
			}
			catch(e){}
		}
		_$.obj=obj;
		return _$;
	},
	stringify:function(json){ //json对象转等式字符串
		var s='';
		for(var i in json) s+=i+'='+json[i]+'&'
	},
	verification:function(type,value,condition){ //表单验证
		var myreg;
		if(type=='telphone'){ //手机号
			myreg = /^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1}))+\d{8})$/;
			if(myreg.test(value)) 
			    return true;
			else
				return false;
		}
		else if(type=='email'){ //邮箱验证
			myreg =/^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
			if(myreg.test(value)) 
			    return true;
			else
				return false;
		}
		else if(type=='number'){ //数字
			var num = parseInt(num);
			if(num == typeof 'number'){
				// lt< , gt>
				var lt = condition.lt || (num+=1),
					gt = condition.gt || (num-=1);
				if(num < lt && num > gt)
					return true;
				else
					return false;
			}
			else
				return false;
		}
		else if(type='sex'){ //性别
			if(value=='男' || value=='女')
				return true;
			else
				return false;
		}
	},
	appAjax:function(json){ //APP ajax
		var t=this;
		try{
			json.type = json.type.toUpperCase();
			json.type = json.type!='GET'?
						(json.type!='POST'?'GET':json.type):json.type;
			var xhr = new plus.net.XMLHttpRequest();
			xhr.onreadystatechange = function () {
				switch ( xhr.readyState ) {
					case 4:
						if ( xhr.status == 200 ) {
							// xhr.responseText
							json.onload(xhr.responseText);
						}
						break;
					default :
						break;
				}
			}
			xhr.onerror=function(e){ //错误回调
				// e.lengthComputable
				// e.loaded
				// e.total;
				json.onerror(e);
			}
			xhr.timeout = json.timeout; //超时时间（毫秒）
			xhr.ontimeout = function(e){ //超时回调
				// e.lengthComputable
				// e.loaded
				// e.total;
				json.ontimeout(e);
			}
			if(json.type=='GET'){
				xhr.open( "GET", json.url+'?'+t.stringify(json.data) );
				xhr.send();
			}
			else{
				xhr.open( "POST", json.url);
				xhr.send();
			}
		}
		catch(e){}
	}
	
};
