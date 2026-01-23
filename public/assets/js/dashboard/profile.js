(function($){

var dzProfile = function(){
	
	var chartTasksSummary = function(){
		var options = {
			series: [25, 17, 20, 38],
			chart: {
				type: 'donut',
				width: 250,
			},
			plotOptions: {
				pie: {
					donut: {
						size: '90%',
						labels: {
							show: true,
							name: {
								show: true,
								offsetY: 20,
							},
							value: {
								show: true,
								fontSize: '24px',
								fontWeight:'600',
								offsetY: -16,
							},
							total: {
								show: true,
								fontSize: '14px',
								color:'#888888',
								fontWeight:'500',
								label: 'Total',
								
								formatter: function (w) {
									return w.globals.seriesTotals.reduce((a, b) => {
										return a + b
									}, 0)
								}
							}
						}
					}
				}
			},
			legend: {
                show: false,
            },
			colors: [
				'var(--primary)',
				'var(--bs-success)',
				'var(--bs-danger)',
				'var(--bs-warning)',
			],
			labels: [
				"Employee",
				"Present",
				"Absent",
				"Holiday"
			],
			dataLabels: {
				enabled: false,
			},
			stroke: {
				width: 3,
				colors: [
					'var(--bs-white)',
				],
			},
		};
		
		if($('#chartTasksSummary').length > 0){
			var handleTasksSummary = new ApexCharts(document.querySelector("#chartTasksSummary"), options);
			handleTasksSummary.render();			
		}
	}
	
	var chartTopSelling = function(){
		var options = {
			series: [17, 8, 8, 17, 17, 8, 25],
			chart: {
				type: 'donut',
				width: 320,
			},
			plotOptions: {
				pie: {
					donut: {
						size: '90%',
						labels: {
							show: true,
							name: {
								show: true,
								offsetY: 20,
							},
							value: {
								show: true,
								fontSize: '24px',
								fontWeight:'600',
								offsetY: -16,
							},
							total: {
								show: true,
								fontSize: '14px',
								color:'#888888',
								fontWeight:'500',
								label: 'Total',
								
								formatter: function (w) {
									return w.globals.seriesTotals.reduce((a, b) => {
										return a + b
									}, 0)
								}
							}
						}
					}
				}
			},
			legend: {
                show: false,
            },
			colors: [
				'var(--primary)',
				'var(--bs-success)',
				'var(--bs-danger)',
				'var(--bs-warning)',
				'var(--bs-purple)',
				'var(--bs-dark)',
				'var(--bs-secondary)',
			],
			labels: [
				"Laptop",
				"Phone",
				"Keyboard",
				"Mouse",
				"Monitors",
				"Watch",
				"Earbuds",
			],
			dataLabels: {
				enabled: false,
			},
			stroke: {
				width: 3,
				colors: [
					'var(--bs-white)',
				],
			},
			responsive: [
				{
					breakpoint: 1480,
					options: {
						chart: {
							type: 'donut',
							width: 250,
						},
					}
				}
			]
		};
		
		if($('#chartTopSelling').length > 0){
			var handleTopSelling = new ApexCharts(document.querySelector("#chartTopSelling"), options);
			handleTopSelling.render();	
		}
	}
	
	var chartRecentStats = function(){
		var options  = {
			series: [
				{
					name: 'Income',
					data: [80, 105, 140, 115, 130, 150, 85]
				}, 
				{
					name: 'Expense',
					data: [70, 95, 125, 110, 115, 145, 75]
				},
			],
			chart: {
				type: 'bar',
				height: 300,
				
				toolbar: {
					show: false,
				},
			},
			plotOptions: {
				bar: {
					horizontal: false,
					endingShape:'rounded',
					columnWidth: '45%',
					borderRadius: 5,
				},
			},
			colors:['#', '#77248B'],
			dataLabels: {
				enabled: false,
			},
			markers: {
				shape: "circle",
			},
			legend: {
				show: false,
				fontSize: '12px',
				labels: {
					colors: '#000000',
				},
				markers: {
					width: 30,
					height: 30,
					strokeWidth: 0,
					strokeColor: '#fff',
					fillColors: undefined,
					radius: 35,	
				}
			},
			stroke: {
				show: true,
				width: 15,
				colors: ['transparent']
			},
			grid: {
				borderColor: 'var(--border-light)',
			},
			xaxis: {
				categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
				labels: {
					style: {
						colors: '#888888',
						fontSize: '12px',
						cssClass: 'apexcharts-xaxis-label',
					},
				},
				axisBorder: {
					show: false,
				},
				axisTicks: {
					show: false,
					borderType: 'solid',
					color: '#78909C',
					height: 6,
					offsetX: 0,
					offsetY: 0
				},
				crosshairs: {
					show: false,
				}
			},
			yaxis: {
				min: 0,
				max: 150,
				tickAmount: 6,
				labels: {
					offsetX: 0,
					style: {
						colors: '#888888',
						fontSize: '12px',
						cssClass: 'apexcharts-xaxis-label',
					},
				},
			},
			fill: {
				opacity: 1,
				colors:[
					'var(--primary)',
					'var(--bs-gray)'
				],
			},
			tooltip: {
				y: {
					formatter: function (val) {
						return "$ " + val + " thousands"
					}
				}
			},
			responsive: [
				{
					breakpoint: 1480,
					options: {
						stroke: {
							show: true,
							width: 8,
							colors: ['transparent']
						},
					}
				},
				{
					breakpoint: 591,
					options: {
						chart: {
							height: 250,
						},
						stroke: {
							show: true,
							width: 4,
							colors: ['transparent']
						},
					}
				}
			]
		};

		if(jQuery("#chartRecentStats").length > 0){
			var chartRecentStats = new ApexCharts(document.querySelector("#chartRecentStats"), options);
			chartRecentStats.render();
			
			jQuery('.chart-recent-stats-tab .nav-link').on('click',function(){
				if($(this).attr("href") == "#monthly"){
					chartRecentStats.updateSeries([
						{
							name: "Running",
							data: [80, 105, 140, 115, 130, 150, 85]
						},
						{
							name: "Cycling",
							data: [70, 95, 125, 110, 115, 145, 75]
						}
					])
				}else if($(this).attr("href") == "#weekly"){
					chartRecentStats.updateSeries([
						{
							name: "Running",
							data: [55, 85, 135, 125, 145, 120, 65]
						},
						{
							name: "Cycling",
							data: [75, 105, 100, 125, 76, 95, 65]
						}
					])
				}else if($(this).attr("href") == "#today"){
					chartRecentStats.updateSeries([
						{
							name: "Running",
							data: [58, 75, 135, 125, 145, 138, 95]
						},
						{
							name: "Cycling",
							data: [80, 135, 60, 85, 135, 148, 85]
						}
					])
				}else if($(this).attr("href") == "#all"){
					chartRecentStats.updateSeries([
						{
							name: "Running",
							data: [85, 100, 145, 110, 135, 145, 90]
						},
						{
							name: "Cycling",
							data: [75, 100, 120, 115, 120, 140, 80]
						}
					])
				}
			})
		}
	}
	
	var chartSecuritySummary = function(){
		var options  = {
			series: [
				{
					name: 'Income',
					data: [31, 40,  28, 31, 40, 28, 31, 40, 28, 31, 40, 28]
				}, 
				{
					name: 'Expense',
					data: [11, 32, 45, 38, 25, 20, 36, 45, 15, 11, 32, 45]
				},
			],
			chart: {
				type: 'bar',
				height: 280,
				
				toolbar: {
					show: false,
				},
			},
			plotOptions: {
				bar: {
					horizontal: false,
					endingShape:'rounded',
					columnWidth: '45%',
					borderRadius: 5,
				},
			},
			colors:['#', '#77248B'],
			dataLabels: {
				enabled: false,
			},
			markers: {
				shape: "circle",
			},
			legend: {
				show: false,
				fontSize: '12px',
				labels: {
					colors: '#000000',
				},
				markers: {
					width: 30,
					height: 30,
					strokeWidth: 0,
					strokeColor: '#fff',
					fillColors: undefined,
					radius: 35,	
				}
			},
			stroke: {
				show: true,
				width: 6,
				colors: ['transparent']
			},
			grid: {
				borderColor: 'var(--border-light)',
			},
			xaxis: {
				categories: ['Jan', 'Feb', 'Mar','Apr','May','Jun','Jul','Aug', 'Sep', 'Oct','Nov','Dec'],
				labels: {
					style: {
						colors: '#888888',
						fontSize: '12px',
						cssClass: 'apexcharts-xaxis-label',
					},
				},
				axisBorder: {
					show: false,
				},
				axisTicks: {
					show: false,
					borderType: 'solid',
					color: '#78909C',
					height: 6,
					offsetX: 0,
					offsetY: 0
				},
				crosshairs: {
					show: false,
				}
			},
			yaxis: {
				labels: {
					offsetX: 0,
					style: {
						colors: '#888888',
						fontSize: '12px',
						cssClass: 'apexcharts-xaxis-label',
					},
				},
			},
			fill: {
				opacity: 1,
				colors:[
					'var(--primary)',
					'var(--dark)'
				],
			},
			tooltip: {
				y: {
					formatter: function (val) {
						return "$ " + val + " thousands"
					}
				}
			},
			responsive: [{
				breakpoint: 575,
				options: {
					plotOptions: {
						bar: {
							columnWidth: '1%',
							borderRadius: -1,
						},
					},
					chart:{
						height:250,
					},
					series: [
						{
							name: 'Projects',
							data: [31, 40, 28,31, 40, 28,31, 40]
						}, 
						{
							name: 'Projects',
							data: [11, 32, 45,31, 40, 28,31, 40]
						},
					],
				}
			}]
		};
		
		if(jQuery("#chartSecuritySummary").length > 0){
			var handleSecuritySummary = new ApexCharts(document.querySelector("#chartSecuritySummary"), options);
			handleSecuritySummary.render();
			
			jQuery('.chart-summary-tab .nav-link').on('click',function(){
				if($(this).attr("href") == "#summaryAgents"){
					handleSecuritySummary.updateSeries([
						{
							name: "Income",
							data: [31, 40,  28, 31, 40, 28, 31, 40, 28, 31, 40, 28]
						},
						{
							name: "Expense",
							data: [11, 32, 45, 38, 25, 20, 36, 45, 15, 11, 32, 45]
						}
					])
				}else if($(this).attr("href") == "#summaryClients"){
					handleSecuritySummary.updateSeries([
						{
							name: "Income",
							data: [25, 45,  26, 32, 46, 22, 34, 46, 26, 24, 38, 27]
						},
						{
							name: "Expense",
							data: [18, 38, 36, 24, 23, 26, 25, 42, 18, 15, 26, 32]
						}
					])
				}
			})
		}	
	}
	
	var chartTasksOverTime = function(){
		var options = {
			series: [
				{
					name: 'Incomplete',
					data: [90, 120, 120, 100, 100, 90]
				},
				{
					name: 'Comple',
					data: [50, 75, 75, 55, 55, 70]
				}
			],
			chart: {
				height: 280,
				type: 'area',
				toolbar:{
					show: false
				},
			},
		  	colors:[
				"var(--bs-danger)",
				"var(--primary)"
			],
			dataLabels: {
				enabled: false
			},
			stroke: {
				curve: 'smooth',
				width: 3,
			},
			legend:{
				show: false,
			},
			grid:{
				show: true,
				strokeDashArray: 3,
				borderColor: 'var(--border-light)',
			},
			yaxis: {
				min: 0,
				max: 120,
				tickAmount: 4,
				labels: {
					style: {
						colors: '#888888',
						fontSize: '14px',
					},
					formatter: function (value) {
						return value;
					}
				},
			},
			xaxis: {
				categories: ["Jun", "Jul", "Aug", "Sep", "Oct", "Nov"],
				labels:{
					style: {
						colors: '#888888',
						fontSize: '12px',
					},
				},
				axisTicks : {
					show : false
				},
				axisBorder : {
					show : false
				},
			},
			fill:{
				type:'gradient',
				gradient: {
					colorStops:[ 
						[
							{
								offset: 0,
								color: 'var(--bs-danger)',
								opacity: 0.2
							},
							{
								offset: 50,
								color: 'var(--bs-danger)',
								opacity: 0.1
							},
							{
								offset: 80,
								color: 'var(--bs-danger)',
								opacity: 0
							}
						],
						[
							{
								offset: 0,
								color: 'var(--primary)',
								opacity: 0.2
							},
							{
								offset: 0.4,
								color: 'var(--primary)',
								opacity: 0.2
							},
							{
								offset: 100,
								color: 'var(--primary)',
								opacity: 0.2
							}
						],
					]
				},				
			},
			tooltip: {
				x: {
					format: 'dd/MM/yy HH:mm'
				},
			},
			responsive: [{
				breakpoint: 575,
				options: {
					chart : {
						height:200,
					},
					stroke :{
						width : 3,
					},
					yaxis: {
						labels:{
							style: {
								fontSize: '11px',
							},
						},
					},
					xaxis: {
						labels:{
							style: {
								fontSize: '11px',
							},
						},
					},
				},
			}]
		};
  
		if(jQuery("#chartTasksOverTime").length > 0){
			var handleTasksOverTime = new ApexCharts(document.querySelector("#chartTasksOverTime"), options);
			handleTasksOverTime.render();
            
            jQuery('#seriesIncomplete').on('change',function(){
                jQuery(this).toggleClass('disabled');
                handleTasksOverTime.toggleSeries('Incomplete');
            });
            
            jQuery('#seriesComple').on('change',function(){
                jQuery(this).toggleClass('disabled');
                handleTasksOverTime.toggleSeries('Comple');
            });
		}
	}
	
	var chartDeals = function(){
		var options = {
			series: [
				{
					name: 'Agents',
					data: [75, 85, 72, 100, 50, 100, 80, 75, 95, 35, 75,100]
				}
			],
			chart: {
				height: 250,
				type: 'area',
				toolbar:{
					show: false
				},
			},
		  	colors:[
				"var(--primary)"
			],
			dataLabels: {
				enabled: false
			},
			stroke: {
				curve: 'smooth',
				width: 3,
			},
			legend:{
				show: false,
			},
			grid:{
				show: true,
				strokeDashArray: 3,
				borderColor: 'var(--border-light)',
			},
			yaxis: {
				labels:{
					show : false
				},
			},
			xaxis: {
				labels:{
					show : false
				},
				axisTicks : {
					show : false
				},
				axisBorder : {
					show : false
				},
			},
			fill:{
				type:'gradient',
				gradient: {
					colorStops:[ 
						[
							{
								offset: 0,
								color: 'var(--primary)',
								opacity: 0.2
							},
							{
								offset: 0.4,
								color: 'var(--primary)',
								opacity: 0.1
							},
							{
								offset: 100,
								color: 'var(--primary)',
								opacity: 0
							}
						],
					]
				},				
			},
			tooltip: {
				x: {
					format: 'dd/MM/yy HH:mm'
				},
			},
			responsive: [{
				breakpoint: 575,
				options: {
					chart : {
						height:200,
					},
					stroke :{
						width : 3,
					},
					yaxis: {
						labels:{
							style: {
								fontSize: '11px',
							},
						},
					},
					xaxis: {
						labels:{
							style: {
								fontSize: '11px',
							},
						},
					},
				},
			}]
		};
		
		if(jQuery("#chartDeals").length > 0){

			var handleDeals = new ApexCharts(document.querySelector("#chartDeals"), options);
			handleDeals.render();
            
            $(".chart-deals-tab .nav-link").on('click',function(){
				var seriesType = $(this).attr('data-series');
				var columnData = [];
				
				switch(seriesType) {
					case "dealsAgents":
						columnData = [75, 85, 72, 100, 50, 100, 80, 75, 95, 35, 75,100];
						break;
						
					case "dealsClients":
						columnData = [40, 25, 85, 45, 85, 25, 95, 65, 45, 45, 20,12];
						break;
						
					default:
						columnData = [75, 80, 72, 100, 50, 100, 80, 30, 95, 35, 75,100];
				}
				handleDeals.updateSeries([
					{
						name: "Agents",
						data: columnData
					}
				]);
			})
			
		}	
	}
	
	var tableLicenseUsage = function(){
		if($('#tableLicenseUsage').length > 0){
			var table = $('#tableLicenseUsage').DataTable({
				'dom': 'ZBfrltip',
				buttons: [
					{
						extend: 'excel',
						text: '<i class="las la-file"></i> Export Report',
						className: 'btn btn-primary tp-btn-light btn-sm mb-0 me-0'
					}
				],
				searching: false,
				pageLength: 10,
				select: false,            
				lengthChange: false,
				paging: true,
				bInfo: true,
				language: {
					paginate: {
						next: '<i class="fa-solid fa-angle-right"></i>',
						previous: '<i class="fa-solid fa-angle-left"></i>' 
					}
				},
				initComplete: function() {
					var btns = $('#tableLicenseUsage_wrapper .dt-buttons').detach();
					$('#licenseUsageExcelBTN').append(btns);
				}
			});	
		}
	}
	
	var tableLogs = function(){
		if($('#tableLogs').length > 0){
			var table = $('#tableLogs').DataTable({
				'dom': 'ZBfrltip',
				buttons: [
					{
						extend: 'excel',
						text: '<i class="las la-file"></i> Export Report',
						className: 'btn btn-primary tp-btn-light btn-sm mb-0 me-0'
					}
				],
				searching: false,
				pageLength: 9,
				select: false,            
				lengthChange: false,
				paging: true,
				bInfo: true,
				language: {
					paginate: {
						next: '<i class="fa-solid fa-angle-right"></i>',
						previous: '<i class="fa-solid fa-angle-left"></i>' 
					}
				},
				initComplete: function() {
					var btns = $('#tableLogs_wrapper .dt-buttons').detach();
					$('#tableLogsExcelBTN').append(btns);
				}
			});
		}
	}
	
	var cardCarousel = function(){
		if($('.card-carousel').length > 0){
			$('.card-carousel').owlCarousel({
				items:1,
				loop:true,
				margin:5,
				nav:false,
				autoplay:true,
				autoplayTimeout: 3000,
				autoplayHoverPause:true
			});
		}
	}

	var chartProjectChart = function(){
		var options = {
			series: [30, 40, 20, 10],
			chart: {
				type: 'donut',
				width: 250,
			},
			plotOptions: {
				pie: {
					donut: {
						size: '90%',
						labels: {
							show: true,
							name: {
								show: true,
								offsetY: 12,
							},
							value: {
								show: true,
								fontSize: '24px',
								fontFamily:'Arial',
								fontWeight:'500',
								offsetY: -17,
							},
							total: {
								show: true,
								fontSize: '11px',
								fontWeight:'500',
								fontFamily:'Arial',
								label: 'Total projects',
							   
								formatter: function (w) {
									return w.globals.seriesTotals.reduce((a, b) => {
										return a + b
									}, 0)
								}
							}
						}
					}
				}
			},
			legend: {
                show: false,
            },
			colors: [
				'#FF9F00',
				'var(--primary)',
				'#3AC977',
				'#FF5E5E'
			],
			labels: [
				"Compete",
				"Pending",
				"Not Start"
			],
			dataLabels: {
				enabled: false,
			},
        };
		
		if($('#chartProjectChart').length > 0){
			var handleProjectChart = new ApexCharts(document.querySelector("#chartProjectChart"), options);
			handleProjectChart.render();
		}
		
	}
	
	var lineChartSecuritySummary = function(){
		
		var chartWidth = $("#lineChartSecuritySummary").width();
		
		var options = {
			series: [
				{
					name: 'Net Profit',
					data: [700, 650, 680, 590, 720, 610, 760, 530, 610],
				},
			],
			chart: {
				type: 'area',
				height: 280,
				width: chartWidth + 55,
				toolbar: {
					show: false,
				},
				offsetX: -45,
				zoom: {
					enabled: false
				},
			},
			colors:[
				'var(--primary)'
			],
			dataLabels: {
				enabled: false,
			},
			legend: {
				show: false,
			},
			stroke: {
				show: true,
				width: 2,
				curve:'straight',
				colors:['var(--primary)'],
			},
			grid: {
				show:true,
				borderColor: '#eee',
				
				xaxis: {
					lines: {
						show: true
					}
				},   
				yaxis: {
					lines: {
						show: false
					}
				},  
			},
			yaxis: {
				show: true,
				tickAmount: 4,
				min: 0,
				max: 800,
				labels:{
					offsetX:50,
				}
			},
			xaxis: {
				categories: [
					'May',
					'June',
					'July',
					'Aug',
					'Sep',
					'Oct',
					'Nov',
					'Dec'
				],
				overwriteCategories: undefined,
				
				axisBorder: {
					show: false,
				},
				axisTicks: {
					show: false
				},
				labels: {
					show: true,
					offsetX: 25,
					
					style: {
						fontSize: '12px',
					}
				},
			},
			fill: {
				opacity: 0.5,
				colors:'var(--primary)',
				type: 'gradient', 
				gradient: {
					colorStops:[
						{
							offset: 0.6,
							color: 'var(--primary)',
							opacity: .2
						},
						{
							offset: 0.6,
							color: 'var(--primary)',
							opacity: .15
						},
						{
							offset: 100,
							color: 'var(--primary)',
							opacity: 0
						},
					],
				}
			},
			tooltip: {
				enabled:true,
				style: {
					fontSize: '12px',
				},
				y: {
					formatter: function(val) {
						return "$" + val + ""
					}
				}
			}
		};
		
		if($('#lineChartSecuritySummary').length > 0){
			var handleLineChartSecuritySummary = new ApexCharts(document.querySelector("#lineChartSecuritySummary"), options);
			handleLineChartSecuritySummary.render();
		}
		
		$(".chart-summary-tab .nav-link").on('click',function(){
			var seriesType = $(this).attr('data-series');
			var columnData = [];
			switch(seriesType) {
				case "agents":
					columnData = [700, 650, 680, 590, 720, 610, 760, 530, 610];
					break;
				case "clients":
					columnData = [680, 620, 540, 680, 640, 655, 730, 580, 620];
					break;
				default:
					columnData = [700, 650, 680, 590, 720, 610, 760, 530, 610];
			}
			handleLineChartSecuritySummary.updateSeries([
				{
					name: "Net Profit",
					data: columnData
				}
			]);
		})
	 
	}
	
	var chartProfileProgress = function(){
		var options = {
			series: [
				{
					name: 'Net Profit',
					data: [100,300, 200, 250, 200, 240, 180,230,200, 250, 300],
				},
			],
			chart: {
				type: 'area',
				height: 100,
				toolbar: {
					show: false,
				},
				zoom: {
					enabled: false
				},
				sparkline: {
					enabled: true
				}
			},
			colors:[
				'var(--primary)'
			],
			dataLabels: {
				enabled: false,
			},
			legend: {
				show: false,
			},
			stroke: {
				show: true,
				width: 2,
				curve:'straight',
				colors:['var(--primary)'],
			},
			grid: {
				show:false,
				borderColor: '#eee',
				padding: {
					top: 0,
					right: 0,
					bottom: 0,
					left: -1
				}
			},
			states: {
                normal: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                hover: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                active: {
                    allowMultipleDataPointsSelection: false,
                    filter: {
                        type: 'none',
                        value: 0
                    }
                }
            },
			xaxis: {
				categories: [
					'Jan',
					'feb',
					'Mar',
					'Apr',
					'May',
					'June',
					'July',
					'August',
					'Sept',
					'Oct'
				],
				axisBorder: {
					show: false,
				},
				axisTicks: {
					show: false
				},
				labels: {
					show: false,
					style: {
						fontSize: '12px',
					}
				},
				crosshairs: {
					show: false,
					position: 'front',
					stroke: {
						width: 1,
						dashArray: 3
					}
				},
				tooltip: {
					enabled: false,
					formatter: undefined,
					offsetY: 0,
					style: {
						fontSize: '12px',
					}
				}
			},
			yaxis: {
				show: false,
			},
			fill: {
				opacity: 0.9,
				colors:'var(--primary)',
				type: 'gradient', 
				gradient: {
					colorStops:[
						{
							offset: 0,
							color: 'var(--primary)',
							opacity: .4
						},
						{
							offset: 0.6,
							color: 'var(--primary)',
							opacity: .4
						},
						{
							offset: 100,
							color: 'white',
							opacity: 0
						}
					],
				}
			},
			tooltip: {
				enabled:true,
				style: {
					fontSize: '12px',
				},
				y: {
					formatter: function(val) {
						return "$" + val
					}
				}
			}
		};

		if($('#chartProfileProgress').length > 0){
			var handleProfileProgress = new ApexCharts(document.querySelector("#chartProfileProgress"), options);
			handleProfileProgress.render();
		}
	 
	}
	
	/* Function ============ */
	return {
		
		load:function(){
			chartTasksSummary();
			chartTopSelling();
			chartRecentStats();
			chartSecuritySummary();
			chartTasksOverTime();
			chartDeals();
			tableLicenseUsage();
			tableLogs();
			cardCarousel();
			chartProfileProgress();
			chartProjectChart();
			lineChartSecuritySummary();
		},
		
		resize:function(){
			lineChartSecuritySummary();
		}
	}
	
}();

jQuery(window).on('load',function(){
	setTimeout(function(){
		dzProfile.load();
	}, 1000); 
});

jQuery(window).on('resize',function () {
	setTimeout(function(){
		dzProfile.resize();
	}, 1000);
	
});

})(jQuery);
