
angular.module('delr1', [])
	.controller('GalleryCtrl', function($scope,$http) {
		if (token!=""){
			//console.log("xauth " + token)
			$http.defaults.headers.get = { 'X-AUTH-TOKEN' : token };
		}
		$scope.alert=function(msg) {
			alert(msg);
		}


		$scope.requestImage = function(url,element) {

			$http.get(url)
				.success(function(res){
					element.src=URL.createObjectURL(res);
					element.onload = () => {
                        URL.revokeObjectURL(element.src);
                    }
				});



		}

   	 	$scope.actShowPhoto = function(id) {
			$scope.back=$(window).scrollTop();
	   		$scope.showphoto=true;
			param='';

			$http.get('/api/v1/albums/'+id)
       				.success(function(res){
						  $scope.album = res;
					$scope.videotab=$scope.album.youtube.match(/[^\r\n]+/g);
					if ($scope.videotab!=null){
						$scope.videotabshow=true;

						var myvideogallery = new ddyoutubeGallery({
						        sliderid: 'videojukebox',
						        selected: 0, // default selected video within playlist (0=1st, 1=2nd etc)
						        autoplay: 0, // 0 to disable auto play, 1 to enable
						        autocycle: 1, // 0 to disable auto cycle, 1 to auto cycle and play each video automatically
						        playlist: $scope.videotab // list of youtube video IDs. It's the last segment within a shareable Youtube URL
						});
						$("#videojukebox").show();

					}
					else {
						$scope.videotabshow=false;
						$("#videojukebox").hide();
					}

					$http.get('/api/v1/albums/'+id+'/photos')
					  .success(function(res){
						    $scope.photos=res;
							$('#videoimg').effect( "shake");
					  });


	        		});
				setTimeout( "$('#backimg').effect('shake');",1000 );
				setTimeout( "$('#zipimg').effect('shake');",2000 );
				console.log($scope.gallery.gallery);
				if ($scope.gallery.gallery == undefined)
					return;

				if ($scope.gallery.gallery.video)
					setTimeout( "$('#videoimg').effect('shake');",4000 );
				if ($scope.videotabshow)
					setTimeout( "$('#videoimg2').effect('shake');",4000 );
				setTimeout( "$('#diap').effect('shake');",3000);

    		};
		

		$scope.goToVideo = function() {
			$(document).scrollTop($('#videobox').offset().top);
		}

		$scope.actShowPage = function(page) {
				if (page<$scope.numAlbums)
						$scope.startGal=page;
				$scope.displayGal();
		};

		$scope.showVideo = function() {
				  $( "#dialog" ).dialog( "open" );

                                  param="/videos.php?";
                                  if (hashgal)
                                        param+='alphanum_hash='+hashgal+'/'+$scope.gallery.gallery.id+'&';
                                  param+='id_gal='+$scope.gallery.gallery.id;
                                  so.addVariable('playlistfile',param);
                                  so.addVariable('file',param);
                                  so.addVariable('frontcolor','000000');
                                  so.addVariable('lightcolor','cc9900');
                                  so.addVariable('screencolor','000000');
                                  so.addVariable('plugins','grid-1');
                                  so.addVariable('dock', 'true');
                                  so.addVariable('skin', '/video/skin/classic.zip');

                                  so.write('mediaspace');


		}
	
		$scope.diaporama = function() {
			$("#pht0").trigger("click");
			setTimeout(function(){
				$("#fullsized_fullscreen").trigger("click");
				$("#fullsized_play_id").trigger("click");
			}, 1000);
		}
		$scope.updatesearch = function(val) {
				if (val.length>2){
					$scope.filter=val;
					$scope.startGal=0;
				} else 
					$scope.filter="";
				$scope.displayGal();
		};

		$scope.displayGal = function(){
			console.log("start gal "+$scope.startGal)
			$("#polaroid").width(Math.floor($("#polaroid").width() / 260)*260);
					if ($scope.startGal!=0) {
						$page="&page="+btoa(JSON.stringify({"page":$scope.startGal+1,"limit":$scope.galLimit}));
					} else {
						$page='';
					}
	                $http.get('/api/v1/albums?num_start='+$scope.startGal+$page+'&keyword='+$scope.filter)
        	                .then(function(res){
                	        $scope.albums = res.data.data;
							$scope.numAlbums    = res.data.meta.total_items;
				$scope.link=[];
				i=0;
				
				while(i*$scope.galLimit<$scope.numAlbums)
				{	
					$scope.link.push({indice:i,selected:(i==$scope.startGal)});
					i+=1;
				}
				console.log($scope.link)
                });

		}


		$scope.nextPage=function() {
			$scope.startGal+=1;
			if($scope.startGal*$scope.galLimit>$scope.numAlbums)
				$scope.startGal = $scope.startGal-1;
			$scope.displayGal();
		}

		$scope.previousPage=function() {
				$scope.startGal-=1;
				if ($scope.startGal<0)
						$scope.startGal=0;
				$scope.displayGal();
		}

		$scope.returnToGal = function () {
			$scope.showphoto=0;
			$(window).scrollTop($scope.back);
		}

		$scope.$on('ngRepeatFinished', function (ngRepeatFinishedEvent) {
			if ($scope.showphoto){
				$('#fullsized_go_next').off('click')
        			$('#fullsized_go_prev').off('click')
				$('a.fullsizable').fullsizable({
				detach_id: 'phctrl',
				clickBehaviour: 'next',
				closeButton: true,
                downloadLink: true,
				navigation: true,
				openOnClick: true,
				reloadOnOpen: false
      				});
				window.scrollTo(0,0);
				$scope.$broadcast('scroll.scrollTop')
			}
	
		});


                $scope.startGal=0;
                $scope.galLimit=30;
                $scope.numGal=0;
                $scope.gallery={};
                $scope.link=[];
                $scope.filter="";
		$scope.token=token;
		if (idgal) {
                	$scope.showphoto=true;
			$scope.hash=hashgal;
			$scope.actShowPhoto(idgal);
		}
		else {
			$scope.showphoto=false;
                	$scope.displayGal();
			if (idgal) {
				$scope.actShowPhoto(idgal);
				idgal=false;
			}
			
		}
	})

	.directive('onFinishRenderFilters', function ($timeout) {
		return {
			restrict: 'A',
			link: function (scope, element, attr) {
				if (scope.$last === true) {
					$timeout(function () {
						scope.$emit('ngRepeatFinished');
					});
				}
			}
		}
	});


	
