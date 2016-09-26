function kokoAPI(config){
	if(typeof config == 'undefined'){
		config={};
	}
	if(typeof layer == 'undefined'){
		var ua=navigator.userAgent.toLowerCase();
		if(ua.indexOf("android")>0||ua.indexOf("phone")>0){
			$(document.head).append("<script src='/Public/plugin/layer.m/layer.js'></script>");
		}else{
			$(document.head).append("<script src='/Public/plugin/layer/layer.js'></script>");
		}
		
	}
	var tools=new kokoTools();
	if(typeof config.edit != 'undefined'){
		var edit=config.edit;
		if(typeof edit.url == 'undefined'){
			edit.url='/';
		}
		if(typeof edit.s == 'undefined'){
			edit.s='kokoEdit';
		}
		var kokoEdit=$('.'+edit.s);
		kokoEdit.click(function(){
			var e=$(this);
			if(e.hasClass("kokoEditing")==false){
				e.addClass("kokoEditing");
				v=e.html();
				e.attr("kokoSaved",encodeURIComponent(e.html()));
				e.html("<input type='text' style='height:20px;line-height:20px;min-width:50px;width:"+e.width()+"px'>");
				input=e.find("input");
				input.val(v);
				var func=0;
				if(typeof e.attr("check")!='undefined' && typeof window[e.attr("check")] == 'function'){
					func=window[e.attr("check")];
				}
				input.blur(function(){
					a=$(this).parent();
					if(this.value && ( ( func !=0 && func(this.value) ) || func==0) ){
						var v=this.value;
						if(e.attr("name")){
							$.post(edit.url,{table:e.parents(".kokoTable").attr("table"),id:e.parents(".kokoId").attr("id"),value:this.value,key:e.attr("name"),action:'update'},function(ret){
								if(ret.state==1){
									a.html(v);
								}else{
									layer.alert(ret.msg);
								}
								e.removeClass("kokoEditing");
							});
						}else{
							a.html(v);
							e.removeClass("kokoEditing");
						}
					}else{
						a.html(decodeURIComponent(e.attr("kokoSaved")));
						e.removeClass("kokoEditing");
					}
				});
				input[0].focus();
				input.keypress(function(e){
					if(e.keyCode==13){
						input.blur();
					}
				});
				this.title = '';
				e.css("background",e.attr("background"));
			}
		});
		kokoEdit.hover(function(){
			e=$(this);
			if(e.hasClass("kokoEditing"))return;
			this.title = '点击修改内容';
			e.attr("background",e.css("background"));
			e.css("background","#278296");
		},function(){
			e=$(this);
			this.title = '';
			e.css("background",e.attr("background"));
		});
	}
	/////////////////////////////////switch
	if(typeof config.switch != 'undefined'){
		var sw=config.switch;
		if(typeof sw.class == 'undefined'){
			sw.class='kokoSwitch';
		}
		var kokoSwitch=$("."+sw.class);
		for(var i=0;i<kokoSwitch.length;i++){
			var j=kokoSwitch.eq(i);
			if(kokoSwitch[i].tagName=="IMG" && j.hasClass("inited")==false && typeof j.attr("cur")!="undefined"){
				kokoSwitch[i].src=j.attr(j.attr("cur"));
				j.addClass("inited");
			}
		}
		function changeState($this){
			var e=$($this);
			if($this.tagName=="IMG"){
				var on=0,off=0;
				if(typeof sw.on!="undefined"){
					on=sw.on;
				}
				if(typeof sw.off!="undefined"){
					off=sw.off;
				}
				if(typeof e.attr("on")!="undefined"){
					on=e.attr("on");
				}
				if(typeof e.attr("off")!="undefined"){
					off=e.attr("off");
				}
				if(on==0){
					alert("u need set attributes on");
					return;
				}
				if(off==0){
					alert("u need set attributes off");
					return;
				}
				if(e.attr("cur")=='on'){
					$this.src=off;
					e.attr("cur","off");
				}
				else {
					$this.src=on;
					e.attr("cur","on");
				}
				
			}else{
				if(typeof sw.on == 'undefined'){
					alert("u need set class on");
					return;
				}
				if(typeof sw.off == 'undefined'){
					alert("u need set class off");
					return;
				}
				if(e.hasClass(sw.on)){
					e.removeClass(sw.on);
					e.addClass(sw.off);
				}else if(e.hasClass(sw.off)){
					e.removeClass(sw.off);
					e.addClass(sw.on);
				}else{
					alert("u need set class on or off");
					return;
				}
			}
		}
		kokoSwitch.click(function(){
			 var e=$(this);
			 changeState(this);
			if(typeof sw.callback != 'undefined'){
				var ret=sw.callback();
				if(ret===false)changeState(this);
			}
			if(typeof e.attr('callback') != 'undefined'){
				var ret=window[e.attr('callback')](e);
				if(ret===false)changeState(this);
			}
		});
		
	}
	///////////////////////////////////// tab
	if(typeof config.tab != 'undefined'){
		var tab=config.tab;
		if(typeof tab.from == 'undefined'){
			tab.from='tabFrom';
		}
		if(typeof tab.to == 'undefined'){
			tab.to='tabTo';
		}
		if(typeof tab.cur == 'undefined'){
			tab.cur='cur';
		}
		if(typeof tab.curOld == 'undefined'){
			tab.curOld='cur-back';
		}
		var kokoTab=$('.'+tab.from);
		var kokoTabTo=$('.'+tab.to);
		
		if(typeof tab.init != 'undefined'){
			var cur=0;
			for(var i=0;i<kokoTab.length;i++){
				if(kokoTab.eq(i).hasClass(tab.cur)){
					cur=i;
					break;
				}
			}
			kokoTab.removeClass(tab.cur);
			kokoTab.eq(cur).addClass(tab.cur);
			kokoTabTo.hide();
			kokoTabTo.eq(cur).show();
		}
		function tabFun(e){
			kokoTab.removeClass(tab.cur);
			kokoTab.attr("notcss",0);
			e.attr("notcss",1);
			if(typeof tab.curOld != 'undefined'){
				kokoTab.addClass(tab.curOld);
				e.removeClass(tab.curOld);
			}
			e.addClass(tab.cur);
			kokoTabTo.hide();
			kokoTabTo.eq(e.index()).show();
		}
		kokoTab.click(function(){
				tabFun($(this));
			});
		if(typeof tab.hover != 'undefined'){
			switch(tab.hover){// hover的时候效果
				case 'click':// 点击
					kokoTab.hover(function(){
						tabFun($(this));
					});
					break;
				case 'css':// 根据hover切换
					for(var i=0;i<kokoTab.length;i++){
						var cur=kokoTab.eq(i);
						if(cur.hasClass(tab.cur)){
							cur.attr("notcss",1);
						}
					}
					kokoTab.hover(function(){
						var e=$(this);
						if(e.attr("notcss")!=1){
							e.addClass(tab.cur);
							if(typeof tab.curOld != 'undefined'){
								e.removeClass(tab.curOld);
							}
						}
					},
					function(){
						var e=$(this);
						if(e.attr("notcss")!=1){
							e.removeClass(tab.cur);
							if(typeof tab.curOld != 'undefined'){
								e.addClass(tab.curOld);
							}
						}
					});
					break;
			}
		}
	}
	///////////////////////////////
	if(typeof config.wx_upload != 'undefined'){
		var upload=config.wx_upload;
		if(typeof upload.click == 'undefined'){
			upload.click='kokoUpload';
		}
		var wxUrl='http://res.wx.qq.com/open/js/jweixin-1.0.0.js';
		var s=$("script");
		if(typeof found_wx=='undefined'){
			found_wx=0;
		}
		for(var i=0;i<s.length;i++){
			if(s.attr('src')==wxUrl){
				found_wx=1;
				break;
			}
		}
		if(found_wx==0){
			found_wx=1;
			$(document.head).append("<script id='wxjs' src='"+wxUrl+"'></script>");
			var wxConfig=function(){
				if(typeof wx == "object"){
					$.post("/index.php/Admin/Upload/getWx",{action:'init',url:document.URL},function(e){
						e=JSON.parse(e);
						wx.config({
							debug: false,
							appId: e.appId,
							timestamp: e.timestamp,
							nonceStr: e.nonceStr,
							signature: e.signature,
							jsApiList: ['checkJsApi','chooseImage','uploadImage']
						  });
					});
					clearInterval(wxConfigIndex);
				}
			}
			wxConfigIndex=setInterval(wxConfig,200);
		}
		var kokoUpload=$('.'+upload.click);
		kokoUpload.click(function(){
			var input=$(this).attr("input");
			var img=$(this).attr("img");
			wx.chooseImage({
			    count: 1, // 默认9
			    sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
			    sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
			    success: function (res) {
			        var localIds = res.localIds; // 返回选定照片的本地ID列表，localId可以作为img标签的src属性显示图片
			        wx.uploadImage({
			            localId: localIds[0], // 需要上传的图片的本地ID，由chooseImage接口获得
			            isShowProgressTips: 1, // 默认为1，显示进度提示
			            success: function (res) {
							var quan=layer.load(2, {shade: false,content:'上传中请等待'});
			                var serverId = res.serverId; // 返回图片的服务器端ID
			                $.post('/index.php/Admin/Upload/getWx',{action:'get',serverId:serverId},function(e){
			                	var json=JSON.parse(e);
								if(json.state=='SUCCESS'){
									input=input?input:'input:hidden';
									img=img?img:'img';
									$(input).val(json.url);
									$(img).attr("src",json.url);
									$(img).load(function(){
										layer.close(quan);
									});
								}else{
									layer.close(quan);
								}
			                });
			            }
			        });
			    }
			});
		});
	}
	///////////////////////////////
	if(typeof config.upload != 'undefined'){
		var input='<input type="file" accept="image/*" name="upfile">';
		var upload=config.upload;
		if(typeof upload.click == 'undefined'){
			upload.click='kokoUpload';
		}
		var kokoUpload=$('.'+upload.click);
		var multiple=false;
		if(typeof upload.url == 'undefined'){
			//http://localhost/Public/plugin/ueditor/php/controller.php?action=uploadimage
			upload.url='/Public/plugin/ueditor/php/controller.php';
		}else if(upload.url=='upload'){
			upload.url='/index.php/Admin/Upload/upload';
		}else if(upload.url=='upload2'){
			multiple=true;
			upload.url='/index.php/Admin/Upload/upload2';
			input='<input type="file" multiple="multiple" accept="image/*" name="upfile[]">';
			for(var i=0;i<kokoUpload.length;i++){
				var c=kokoUpload.eq(i);
				if(typeof c.attr('model') == "undefined"){
					alert("need model");
					return;
				}
				var v=$("."+c.attr('model'));
				tools.menuRemove(v);
				v=v.eq(0);
				v.hide();
				var id="id"+Math.round(Math.random()*10000000000);
				v.attr("id",id);
				c.attr('model_id',id);
				$(document.head).append(v);
				if(typeof c.attr('insertBefore') == "undefined"){
					alert("need insertBefore");
					return;
				}			
			}
		}
		if(typeof upload.type == 'undefined'){
			upload.type='image';
		}
		var upload_koko="upload_koko"+(multiple?1:0);
		var div='<div id="'+upload_koko+'" style="display:none;"><form target="upload_f" method="POST" enctype="multipart/form-data" action="'+upload.url+'?action=uploadimage" >'+input+'</form></div>';
		kokoUpload.click(function(){
			$("#"+upload_koko).remove();
			$(document.body).append(div);
			if($("#upload_f").length==0){
				f=document.createElement("iframe");
				f.style.display="none";
				f.style.width="0px";
				f.id="upload_f";
				f.name=f.id;
				$("body").append(f);	
			}
			var up=$(this);
			var input=$(this).attr("input");
			var img=$(this).attr("img");
			input=input?input:'input:hidden';
			img=img?img:'img';
			var callback=$(this).attr("callback");
			var f=$("#"+upload_koko+" form input[type=file]");
			f.attr("accept",upload.type+"/*");
			var click=this;
			f.change(function(){
				if(this.value=='')return;
				if(typeof $(click).attr("size") != 'undefined'){
					size=parseInt( $(click).attr("size"));
				}else if(typeof upload.size != 'undefined'){
					size=parseInt( upload.size );
				}else{
					size=null;
				}
				if(size!=null && !isNaN(size) && size!=''){
					var files=this.files;
					for(var q=0;q<files.length;q++){
						if(files[q].size>size){
							layer.alert("您选择的图片:"+files[q].name+",大小为"+files[q].size +'KB，请选择小于'+size+'KB以下的图片');
							return;
						}
					}				
				}
				var ifr=$("#upload_f")[0];
				var quan=layer.load(2, {shade: false});
				var iframeload=function(){
					if (!ifr.readyState || ifr.readyState == "complete") { 
						var json=$(ifr.contentDocument.body).html();
						json=JSON.parse(json);
						if(multiple){
							var m=$("#"+up.attr('model_id'));
							for(var i=0;i<json.length;i++){
								if(json[i].state=='SUCCESS'){
									console.log(json[i]);
									if(typeof up.attr('container')!= "undefined" && typeof up.attr('num')!= "undefined"){
										var len=$(up.attr('container')).find("."+up.attr('model')).length;
										if(len>=parseInt(up.attr('num'))){
											continue;
										}
									}
									var c=m.clone();
									c.show();
									c.attr("id",'');
									tools.menuRemove(c);
//									c.addClass("model4del");
//									c.removeClass(up.attr('model'));
									c.find(input).val(json[i].url);
									c.find(img).attr("src",json[i].url);
									if(i==json.length-1){
										c.find(img).load(function(){
											layer.close(quan);
										});
									}
									$(up.attr('insertBefore')).before(c);
									if(callback)window[callback](c,json[i]);	
								}
							}
						}else{
							if(json.state=='SUCCESS'){
								$(input).val(json.url);
								$(img).attr("src",json.url);
								$(img).load(function(){
									layer.close(quan);
								});
								if(callback)window[callback](json);						
							}							
						}
						console.log(json);
					}
					$("#upload_f").remove();
				}
				ifr. onload = ifr. onreadystatechange = iframeload;
				$("#"+upload_koko+" form").submit();
			});
			f[0].click();
		});
		function del(a){
			
		}
	}
	///////////////////////////////////////
	if(typeof config.addCart != 'undefined'){
		var addCart=config.addCart;
		var gid=null;
		if(typeof addCart.url == 'undefined'){
			addCart.url="/index.php/Home/Cart/ajax";
		}
		if(typeof addCart.click == 'undefined'){
			addCart.click="kokoAddCart";
		}
		var a456=$("."+addCart.click);
		if(typeof a456.attr('id') == 'undefined'){
			gid=tools.getFromUrl('id');
			if(gid==null||gid==''){
				alert("请添加商品id");return;
			}
			tools.log("addCart id from url");
		}else{
			gid=a456.attr('id');
			tools.log("addCart id from click");
		}
		if(isNaN(gid)||gid==''){
			alert("请添加正确的商品id");return;
		}
		a456.click(function(){
			var ex_id=0;
			if(typeof a456.attr("goods_ex_id")!='undefined'){
				ex_id=a456.attr("goods_ex_id");
				if(isNaN(ex_id)||ex_id==''){
					ex_id=0;
				}
			}
			$.post(addCart.url,{action:'add','data[goods_id]':gid,'data[goods_ex_id]':ex_id,'data[num]':typeof $(this).attr("num")!='undefined'?$(this).attr("num"):1},function(e){
				if(e.state==1){
					layer.alert("加入购物车成功");
				}
			});
		});
	}
	///////////////////////////////////////
	if(typeof config.addBuy != 'undefined'){
		var addBuy=config.addBuy;
		var gid=null;
		if(typeof addBuy.notUseCart!='undefined'){
			location.href="/index.php/Home/Cart/confirm?gid="+gid;
			return;
		}
		if(typeof addBuy.url == 'undefined'){
			addBuy.url="/index.php/Home/Cart/ajax";
		}
		if(typeof addBuy.jumpTo == 'undefined'){
			addBuy.jumpTo="/index.php/Home/Cart/";
		}
		if(typeof addBuy.click == 'undefined'){
			addBuy.click="kokoBuy";
		}
		var a=$("."+addBuy.click);
		if(typeof a.attr('id') == 'undefined'){
			gid=tools.getFromUrl('id');
			if(gid==null||gid==''){
				layer.alert("请添加商品id");return;
			}
			tools.log("addBuy id from url");
		}else{
			gid=a.attr('id');
			tools.log("addBuy id from click");
		}
		if(isNaN(gid)||gid==''){
			layer.alert("请添加正确的商品id");return;
		}
		var ex_id=0;
		if(typeof a.attr("goods_ex_id")!='undefined'){
			ex_id=a.attr("goods_ex_id");
			if(isNaN(ex_id)||ex_id==''){
				ex_id=0;
			}
		}
		a.click(function(){
			$.post(addBuy.url,{action:'add','data[goods_id]':gid,'data[goods_ex_id]':ex_id,'data[num]':typeof $(this).attr("num")!=='undefined'?$(this).attr("num"):1},function(e){
				if(e.state==1){
					location.href=addBuy.jumpTo;
				}else{
					layer.alert(e.msg);
				}
			});
		});
	}
	///////////////////////////////////////
	if(typeof config.addCollection != 'undefined'){
		var addC=config.addCollection;
		if(typeof addC.url == 'undefined'){
			addC.url="/index.php/Home/Collection/ajax";
		}
		if(typeof addC.click == 'undefined'){
			addC.click="kokoCollect";
		}
		var a=$("."+addC.click);
		var gid=null;
		if(typeof a.attr('id') == 'undefined'){
			gid=tools.getFromUrl('id');
			if(gid==null||gid==''){
				alert("请添加商品id");return;
			}
			tools.log("addCollection id from url");
		}else{
			gid=a.attr('id');
			tools.log("addCollection id from click");
		}
		
		if(isNaN(gid)||gid==''){
			layer.alert("请添加正确的商品id");return;
		}
		var key=a.attr("name");
		if(typeof key=='undefined'){
			key='state';
		}
		a.click(function(){
			$.post(addC.url,{action:'switcher',id:gid,key:key},function(e){
				if(e.state==1){
					layer.msg("操作成功");
					var callback=a.attr("callback");
					if(callback)window[callback](json);	
				}else{
					layer.alert(e.msg);
				}
			});
		});
	}
	///////////////////////////////////////
	if(typeof config.fahuo != 'undefined'){
		var fahuo=config.fahuo;
		if(typeof fahuo.url == 'undefined'){
			fahuo.url="/index.php/Admin/order/addWaybill";
		}
		$(".fahuo").click(function(){
			var cur=$(this);
			layer.prompt({title: "填写运单号", formType: 2},function(text){
				$.post(fahuo.url,{sn:cur.attr('sn'),waybill:text},function(e){
					if(e.state==1){
						location.reload();
					}else{
						layer.alert(e.msg);
					}
				});
			});
		});
	}
	///////////////////////////////////////
	if(typeof config.process != 'undefined'){
		var a=config.process;
		if(typeof a.url == 'undefined'){
			a.url="/index.php/Admin/Member/ajax";
		}
		if(typeof a.click == 'undefined'){
			a.click="kokoProcess";
		}
		$("."+a.click).click(function(){
			var cur=$(this);
			var where=0;
			if(typeof cur.attr('where') != 'undefined'){
				where=cur.attr('where');
			}else if(typeof cur.attr('id') != 'undefined'){
				where=cur.attr('id');
			}else{
				alert("no where");
				return;
			}
			var data={action:'process',where:where};
			if(typeof cur.attr('db') != 'undefined'){
				data.db=cur.attr('db');
			}
			if(typeof cur.attr('state') != 'undefined' && ( cur.attr('state') =='agree' ||  cur.attr('state') =='refuse')){
				data.state=cur.attr('state');
			}else{
				alert("state error");
				return;
			}
			if(typeof cur.attr('prompt') != 'undefined'){
				var prompt_type=0;
				if(typeof cur.attr('prompt_type') != 'undefined'){
					prompt_type=cur.attr('prompt_type');
				}
				layer.prompt({title: cur.attr('prompt'), formType: prompt_type},function(text){
					data['config[prompt]']=1;
					data['config[text]']=text;
					$.post(a.url,data,function(e){
						if(e.state==1){
							location.reload();
						}else{
							layer.alert(e.msg);
						}
					});
				});
			}else{
				$.post(a.url,data,function(e){
					if(e.state==1){
						location.reload();
					}else{
						layer.alert(e.msg);
					}
				});				
			}
		});
	}
	//////////////////////////////////////////
	if(typeof config.smsCode != 'undefined'){
		var code=config.smsCode;
		if(typeof code.url == 'undefined'){
			code.url="/index.php/Home/Index/sendCode";
		}
		if(typeof code.sender == 'undefined'){
			code.sender="sender";
		}
		if(typeof code.telphone == 'undefined'){
			code.telphone="telphone";
		}
		if(typeof code.seconds == 'undefined'){
			code.seconds=120;
		}
		sender=$("#"+code.sender);
		if(sender[0].tagName=='INPUT'){
			sender.setval=sender.val;
		}else{
			sender.setval=sender.html;
		}
		var oldstr=sender.setval();
		sender.click(function(){
			if(sender.setval()!=oldstr){
				return;
			}
			var tel=tools.checkTel("#"+code.telphone);
			if(tel){
				$.post(code.url,{telphone:tel},function(e){
					if(e.state==1){
						var time=code.seconds;
						var fun=function(){
							if(time-->0){
								sender.setval(time+'秒后重发');
								setTimeout(fun,1000);
							}else{
								sender.setval(oldstr);
							}
						}
						setTimeout(fun,1000);
					}
					if(typeof code.callback != 'undefined'){
						window['callback'](e);
					}
				});
			}
		});
	}
}

///////////////////////////////////////////
function kokoTools(){
	this.findVal=function($str){
		if(typeof $str == "string"){
			var str=$($str);
			if(str.length==0){
				layer.alert("没有找到"+$str);
				return false;
			}
			str=str.val();
		}else if(typeof $str == "undefined"){
			layer.alert("空");
			return false;
		}
		else if($str.length==0){
			layer.alert("没有找到节点");
			return false;
		}
		else{
			str=$str.val();
		}
		return str;
	}
	this.checkTel=function (a){
		if((a=this.findVal(a))===false){
			return false;
		}
		if(/^1[3-9][0-9]{9}$/.test(a)===false){
			layer.alert("手机号格式不正确");
			return false;
		}
		return a;
	}
	this.checkEmail=function (a){
		if((a=this.findVal(a))===false){
			return false;
		}
		if(/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/.test(a)===false){
			layer.alert("邮箱格式不正确");
			return false;
		}
		return a;
	}
	this.checkShenfen=function (a){
		if((a=this.findVal(a))===false){
			return false;
		}
		if(/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/.test(a)===false){
			layer.alert("身份证号码格式不正确");
			return false;
		}
		return a;
	}
	this.checkEmpty=function (a,b){
		if((a=this.findVal(a))===false){
			return false;
		}
		if(a.length==0){
			layer.alert(b);
			return false;
		}
		return a;
	}
	this.checkPassword=function (a,b){
		if((a=this.findVal(a))===false){
			return false;
		}
		if(a.length<6){
			layer.alert("密码长度至少为6位");
			return false;
		}
		if(typeof b != 'undefined'){
			if((b=this.findVal(b))===false){
				return false;
			}
			if(a!=b){
				layer.alert("两次密码不一致");
				return false;
			}
		}
		return a;
	}
	this.menuRemove=function(a){
		if(typeof a.smartMenu == 'undefined'){
			$(document.head).append('<script src="/Public/plugin/smartMenu/jquery-smartMenu-min.js"></script><link href="/Public/plugin/smartMenu/smartMenu.css" rel="stylesheet" type="text/css" />');
		}
		$menuData=[[{text:"删除",func:function(){
			var e=$(this);
			e.remove();
		}}]];
		a.smartMenu($menuData);
	}

	this.getFromUrl=function (name)
	{
	     var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
	     var r = window.location.search.substr(1).match(reg);
	     if(r!=null)return  unescape(r[2]); return null;
	}
	this.log=function(a){
		console.log(a);
	}
}