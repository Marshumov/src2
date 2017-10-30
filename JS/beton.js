<script> 
var

CURRENCY = ' руб.',

works={

	title:'<p>Марка бетона:</p>',
	data:{
		1:'М100',
		2:'М150',
		3:'М200',
		4:'М250',
		5:'М300',
		6:'М350',
		7:'М400',
		8:'М550'
		
	},
	
},

params={

	

	len:{
		title:'<p>Выберете подвижность</p>',
		data:{
			1:'П2',
			2:'П3',
			3:'П4'
			
		}
	}
},

offers={

	1:{
		price:0,
		params:{
			len:{
				1:2500,
				2:2150,
				3:1500
			},
			
		}
	},
	
	2:{
		price:2500,
		params:{
			len:{
				1:1200,
				2:1300,
				3:1700
			},
	
		}
	},
	
	3:{
		price:1900,
		params:{
			len:{
				1:1200,
				2:1780,
				3:1499
			},

		}
	},
	
	4:{
		price:1500,
		params:{
			len:{
				1:1400,
				2:1500,
				3:1500
			},

		}
	},
		5:{
		price:0,
		params:{
			len:{
				1:2500,
				2:2150,
				3:1500
			},
			
		}
	},
		6:{
		price:0,
		params:{
			len:{
				1:2500,
				2:2150,
				3:1500
			},
			
		}
	},
		7:{
		price:0,
		params:{
			len:{
				1:2500,
				2:2150,
				3:1500
			},
			
		}
	},
		8:{
		price:0,
		params:{
			len:{
				1:2500,
				2:2150,
				3:1500
			},
			
		}
	}
}


total=0,prodList=calcButton=null;

function resetList(el){
	v=el.querySelectorAll('select');
	for(var i=0;i<v.length;i++)
		v[i].selectedIndex=-1;
}
<!--
function renderParams(ev){

	if(ev.target.nodeName!='SELECT') return;
	var v=ev.target.value;
	if(!(v in offers)) return;
	var offer=offers[v],html=[];

	total=offer.price;


	
for(v in offer.params) {
		var h=[];
		for(var p in offer.params[v])
			h.push(renderOption(offer.params[v][p],params[v].data[p]));
		document.getElementById('parent').innerHTML = ('<label>'+params[v].title+'</label>'
			+'<select class="select_pod">'+h.join('')+'</select>');

	
	
		
		
	

			<!--	html.push(znachenie); -->
	}
	
	prodList.innerHTML=html.join('');
	
	calcButton.disabled=false;
}

function renderOption(v,t){
	return '<option  value="'+v+'">'+t+'</option>';
}

function renderOptions(obj){
	var html=[];
	for(var p in obj)
		html.push(renderOption(p,obj[p]));
	return html.join('');
}

function renderWorks(classname) {
	var el=document.querySelector('.'+classname);
	el.innerHTML='<label class="label">'+works.title+'<label>'
		+'<select class="label_calc">'+renderOptions(works.data)+'</select>';
	resetList(el);
	el.addEventListener('change',renderParams);
}
 
	 <!--  var result = document.getElementById('result').value; -->
 

function renderTotal(){
 var second = document.getElementById('forma2').value;
 if (second<1) {
 second=1;
 }
 var first = document.getElementById('forma1').value; 
  if (first<1) {
 first=1;
 }
	v=prodList.querySelectorAll('select');
	for(var i=t=0;i<v.length;i++)
		t+= +v[i].value;
		document.getElementById('forma3').value = first * second;
	document.querySelector('.m3').value=(((total+t) * first) * second)+CURRENCY;
	 
}
</script> 
<script> 
prodList=document.querySelector('.product-list');
calcButton=document.querySelector('.calc-total');
calcButton.addEventListener('click',renderTotal);
renderWorks('filters-block');
renderWorks('label');
</script> 

