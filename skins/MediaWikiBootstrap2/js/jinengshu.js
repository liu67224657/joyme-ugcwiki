$(document).ready(function() {
	var num1 =0;
var job1 = 0;
var job2 = 0;
	jiadian();

	$('.jns-menu li').click(function(){
		var num = $('.jns-menu li').index(this);
		$('.jns-content > div').hide();
		$('.jns-content > div').eq(num).show();
		num1 = num;
		jiadian();
	})
	/*
	$(".zhuanzhi span").click(function(){
		var num2 = $(this).index();
		$(this).parent(".zhuanzhi").siblings(".job").children("ul").eq(num2).show();
	})
	*/
	 function jiadian(){
	    changeJob();
		initRejob();
	    //hover˵������
	    $('.skill').mouseover(function(){
		    var now = $(this).attr('now');
		    $(this).find('p').eq(now).show();
		}).mouseout(function(){
		    $(this).find('p').hide();
		});
		//ְҵѡ�����仯ʱ����job�ȼ���job����
		$('.jobb').eq(num1).change(function(){
			changeJob();
			initRejob();
			clear();
		})
		//job�ȼ������仯ʱ����job����
		$('.levell').eq(num1).change(function(){
			initRejob();
			clear();
		})
}
	    //�ӵ�
	    $('.skill span').click(function(){
		    var now = $(this).parent('.skill').attr('now');
			var max = $(this).parent('.skill').attr('max');
			var job = $(this).parent('.skill').attr('job');
			var preNum = $(this).parent('.skill').attr('preNum');
		    if(now*1 < max*1 && checkAdd(job)){
			    if(preNum){
				    var preLevel = $(this).parent('.skill').attr('preLevel');
					if(preLevel*1 > $('.jns-content > div').eq(num1).find('.skill').eq(preNum).attr('now')*1){
					    return;
					}
				}
			    $(this).parent('.skill').find('em').html((now*1+1)+'/'+max);
			    $(this).parent('.skill').attr('now', now*1+1);
				$(this).parent('.skill').find('b').show();
				$(this).parent('.skill').find('p').hide();
				$(this).parent('.skill').find('p').eq(now*1+1).show();
				changeRejob(job, 'add');
			}
		})
		
		//�˵�
		$('.skill b').click(function(){
		    var now = $(this).parent('.skill-icon').parent('.skill').attr('now');
			var max = $(this).parent('.skill-icon').parent('.skill').attr('max');
			var job = $(this).parent('.skill-icon').parent('.skill').attr('job');
			var aftNum = $(this).parent('.skill-icon').parent('.skill').attr('aftNum');
		    if(now*1 >0 && checkDel(job)){
			    if(aftNum){
				    var aftLevel = $(this).parent('.skill-icon').parent('.skill').attr('aftLevel');
					if($('.jns-content > div').eq(num1).find('.skill').eq(aftNum).attr('now')*1 > 0 && aftLevel*1 >= now*1){
					    return;
					}
				}
			    $(this).parent('.skill-icon').parent('.skill').find('em').html((now*1-1)+'/'+max);
			    $(this).parent('.skill-icon').parent('.skill').attr('now', now*1-1);
				$(this).parent('.skill-icon').parent('.skill').find('p').hide();
				$(this).parent('.skill-icon').parent('.skill').find('p').eq(now*1-1).show();
				changeRejob(job, 'del');
				if(now*1-1==0){
				    $(this).hide();
				}
			}
		})
	//��������Ų�
	function checkAdd(job){ //job1��һתְҵ��job2�Ƕ�תְҵ
	    var jobval =$(".jobb").eq(num1).val();
		var level = $(".levell").eq(num1).val();
		if(jobval==1 && job==2){
		    return false;
		}
		var sum1 = 0;
		$(".job1").eq(num1).find(".skill").each(function(){
			sum1 += $(this).attr('now')*1;//�õ�.jobb1�����м��ܼӵ������
		})
		var sum2 = 0;
		$(".job2").eq(num1).find(".skill").each(function(){
			sum2 += $(this).attr('now')*1;//�õ�.jobb2�����м��ܼӵ������
		})
		if(job==1){
			if(jobval==1 && sum1>=level){ //ְҵΪһתְҵ����.jobb1�����еĵ���>�ȼ���
			    return false;
			}else if(jobval==2 && sum1>=40+level*1){
			    return false;
			}
		}
		if(job==2){
			if(sum1<40){
			    return false;
			}
			if(jobval==2 && sum1+sum2>=40+level*1){
			    return false;
			}
		}
		return true;
	}
	function checkDel(job){
	    var jobval = $(".jobb").eq(num1).val();
		if(jobval==2 && job==1){
		    var sum1 = 0;
		    $(".job1").eq(num1).find(".skill").each(function(){
			    sum1 += $(this).attr('now')*1;
			})
		    var sum2 = 0;
		    $(".job2").eq(num1).find(".skill").each(function(){
			    sum2 += $(this).attr('now')*1;
			})
			if(sum2>0 && sum1<=40){
			    return false;
			}
		}
		return true;
	}
	//ְҵΪһתʱ�ȼ����40��ְҵΪ��תʱ�ȼ����50
	function changeJob(){
	    var job = $(".jobb").eq(num1).val();
	    var html='';
		var end=0;
	    if(job==1){
		    end = 40;
		}else if(job==2){
		    end = 50;
		}
		for(var i=1; i<=end; i++){
		    html += '<option value ="'+i+'">'+i+'</option>';
		}
		$(".levell").eq(num1).html(html);
	}
	//rejob1��1תʣ��JOB������rejob2��2תʣ��JOB����
	function initRejob(){
	    var jobval =$(".jobb").eq(num1).val();
		var level = $(".levell").eq(num1).val();
	    var rejob1 = jobval==1 && level*1<40 ? level*1 : 40;
		var rejob2 = jobval==2 && level*1<50 ? level*1 : (jobval==1 ? 0 : 50);
		job1 = rejob1;
		job2 = rejob2;
		$('.rejob1').eq(num1).html(rejob1);
		$('.rejob2').eq(num1).html(rejob2);
	}
	//ʣ���������
	function changeRejob(job, change){
	    if(change=='add'){
		    if(job==1 && job1>0){
		        job1 = job1-1;
		    }else if(job==1 && job1==0){
			    job2 = job2-1;
			}else if(job==2 && job2>0){
		        job2 = job2-1;
		    }
		}else if(change=='del'){
		    if(job==2 && job2<50){
		        job2 = job2+1;
		    }else if(job==1 && job2<50){
			    job2 = job2+1;
			}else if(job==1 && job1<40){
			    job1 = job1+1;
			}
		}
		$('.rejob1').eq(num1).html(job1);
		$('.rejob2').eq(num1).html(job2);
	}
	//����
	function clear(){
	    $('.skill').attr('now',0);
		$('.skill .skill-icon b').hide();
		$('.skill').each(function(){
			var max = $(this).attr('max');
			$(this).find('.skill-icon').find('em').html('0/'+max);
		})
	}
	
	  })