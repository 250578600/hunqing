function layout(obj){
	obj=$(obj);
	var leftReg = RegExp('<','g'),
		rightReg = RegExp('>','g');
	var _$={
		obj:undefined,
		clear:function(){ //清空layout数据
			this.obj.removeAttr('layout');
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
			var str = obj.eq(i).attr('layout').replace(leftReg,'"').replace(rightReg,'"');
			var json=eval('({'+str+'})');
			for(var j in json){
				obj.eq(i).css( attr(j), calculation(json[j],obj.eq(i)) );
			}
		}
		catch(e){}
	}
	_$.obj=obj;
	return _$;
}
