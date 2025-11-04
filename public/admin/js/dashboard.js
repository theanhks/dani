(function($){ "use strict";

    /* --- TODO --- */
    $(".tdl-new").on('keypress', function(e){
        if ((e.keyCode ? e.keyCode : e.which) === 13) {
            var v=$(this).val(), s=v.replace(/ +?/g,''); if(!s) return false;
            $(".tdl-content ul").append("<li><label><input type='checkbox'><i></i><span>"+v+"</span><a href='#' class='ti-trash'></a></label></li>");
            $(this).val("");
        }
    });
    $(".tdl-content").on("click","a",function(){
        var _li=$(this).closest("li"); _li.addClass("remove").stop().delay(100).slideUp("fast",function(){_li.remove();});
        return false;
    });

    /* --- SLIMSCROLL --- */
    if($.fn.slimscroll){
        $('#activity').slimscroll({ position:"right", size:"5px", height:"390px", color:"transparent" });
    }

    /* --- DATAMAP (requires d3 v3 + topojson v1 + datamaps) --- */
    if (document.getElementById('world-map') && typeof Datamap !== 'undefined') {
        var map = new Datamap({
            scope:"world",
            element: document.getElementById("world-map"),
            responsive:true,
            geographyConfig:{ popupOnHover:false, highlightOnHover:false, borderColor:"transparent", borderWidth:1 },
            bubblesConfig:{
                popupTemplate: function(geo, data){ return '<div class="datamap-sales-hover-tooltip">'+data.country+'<span class="ml-2"></span>'+data.sold+'</div>'; },
                borderWidth:0, highlightBorderWidth:3, highlightFillColor:"rgba(0,123,255,0.5)", highlightBorderColor:"transparent", fillOpacity:.75
            },
            fills:{ Visited:"#777", neato:"#777", white:"#777", defaultFill:"#EBEFF2" }
        });
        map.bubbles([
            { centered:"USA", fillKey:"white",  radius:5, sold:"$500",  country:"United States" },
            { centered:"SAU", fillKey:"Visited",radius:5, sold:"$900",  country:"Saudia Arabia" },
            { centered:"RUS", fillKey:"neato",  radius:5, sold:"$250",  country:"Russia" },
            { centered:"CAN", fillKey:"white",  radius:5, sold:"$1000", country:"Canada" },
            { centered:"IND", fillKey:"Visited",radius:5, sold:"$50",   country:"India" },
            { centered:"AUS", fillKey:"white",  radius:5, sold:"$700",  country:"Australia" },
            { centered:"BGD", fillKey:"Visited",radius:5, sold:"$1500", country:"Bangladesh" }
        ]);
        window.addEventListener("resize", function(){ map.resize(); });
    }

    /* --- MORRIS (requires raphael + morris) --- */
    if (document.getElementById('morris-bar-chart') && typeof Morris !== 'undefined') {
        Morris.Bar({
            element: 'morris-bar-chart',
            data: [
                { y:'2016', a:100, b:90 },
                { y:'2017', a:75,  b:65 },
                { y:'2018', a:50,  b:40 },
                { y:'2019', a:75,  b:65 },
                { y:'2020', a:50,  b:40 },
                { y:'2021', a:75,  b:65 },
                { y:'2022', a:100, b:90 }
            ],
            xkey: 'y',
            ykeys: ['a','b'],
            labels: ['A','B'],
            barColors: ['#FC6C8E','#7571f9'],
            hideHover: 'auto',
            gridLineColor: 'transparent',
            resize: true
        });
    }

    /* --- CHART.JS --- */
    if (window.Chart) {
        var el2=document.getElementById("chartjs_widget_2");
        if(el2){ el2.height=280;
            new Chart(el2,{ type:'line',
                data:{ labels:["2010","2011","2012","2013","2014","2015","2016"],
                    datasets:[
                        { data:[0,15,57,12,85,10,50], label:"iPhone X", backgroundColor:'#847DFA', borderColor:'#847DFA', borderWidth:.5, pointRadius:5, pointBorderColor:'transparent', pointBackgroundColor:'#847DFA' },
                        { data:[0,30,5,53,15,55,0], label:"Pixel 2", backgroundColor:'#F196B0', borderColor:'#F196B0', borderWidth:.5, pointRadius:5, pointBorderColor:'transparent', pointBackgroundColor:'#F196B0' }
                    ]},
                options:{ responsive:true, maintainAspectRatio:false, legend:{display:false}, scales:{xAxes:[{display:false}], yAxes:[{display:false}]}}
            });
        }
        var el3=document.getElementById("chartjs_widget_3");
        if(el3){ el3.height=130;
            new Chart(el3,{ type:'line',
                data:{ labels:["Jan","Feb","Mar","Apr","May","Jun"],
                    datasets:[{ data:[0,15,57,12,85,10], label:"iPhone X", backgroundColor:'transparent', borderColor:'#847DFA', borderWidth:2, pointRadius:5, pointBorderColor:'#847DFA', pointBackgroundColor:'#fff' }]},
                options:{ responsive:true, maintainAspectRatio:true, legend:{display:false}, scales:{xAxes:[{display:false}], yAxes:[{display:false}]}}
            });
        }
    }

    /* --- CHARTIST --- */
    if (window.Chartist) {
        if (document.getElementById('chartist_line')){
            new Chartist.Line("#chartist_line", {
                labels:["1","2","3","4","5","6","7","8"],
                series:[[4,5,1.5,6,7,5.5,5.8,4.6]]
            }, { low:0, showArea:false, showPoint:true, showLine:true, fullWidth:true, lineSmooth:false,
                chartPadding:{top:4,right:4,bottom:-20,left:4}, axisX:{showLabel:false,showGrid:false,offset:0}, axisY:{showLabel:false,showGrid:false,offset:0} });
        }
        if (document.getElementById('chartist_pie')){
            new Chartist.Pie("#chartist_pie", { series:[35,65] }, { donut:true, donutWidth:10, startAngle:0, showLabel:false });
        }
    }

    /* --- CALENDAR --- */
    if ($('.year-calendar').length && $.fn.pignoseCalendar){
        $(".year-calendar").pignoseCalendar({ theme:"blue" });
    }

})(jQuery);