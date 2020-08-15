
angular.module('delr1', [])
	.controller('GalleryCtrl', function($scope,$http) {
		$scope.alert=function(msg) {
			alert(msg);
		}

   	 	$scope.actShowPhoto = function(id) {
			$scope.back=$(window).scrollTop();
	   		$scope.showphoto=true;
			param='?hash='+hash;
			
			$http.get('/api/v1/publicalbums/'+id+param)
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


	
		$scope.diaporama = function() {
			$("#pht0").trigger("click");
			setTimeout(function(){
				$("#fullsized_fullscreen").trigger("click");
				$("#fullsized_play_id").trigger("click");
			}, 1000);
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
		$scope.showphoto=true;
		$scope.hash=hash;
		$scope.actShowPhoto(idAlbum);
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


	
