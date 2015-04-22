window.vimeography = window.vimeography || {};

/* http://jsfiddle.net/elijahmanor/MVNBz/22/ */
(function(timber, $, undefined) {

  var gallery_id = [];

  timber.set_gallery_id = function(element) {
    gallery_id.push(element);
  };

  /* Have fun! */
  $(document).ready(function() {
    $.each(gallery_id, function(i, id) {

      var $gallery = vimeography.utilities.get_gallery(id);
      var $template = $('.vimeography-gallery-' + id + '.vimeography-template').clone();

      vimeography.utilities.enable_autoplay = 1;

      var opts = {
        padding: 0,
        autoSize: false,
        autoHeight: true,
        fitToView: false,
        mouseWheel: false,
        maxWidth: 960,
        width: '100%',
        closeBtn: false,
        type: 'html',
        scrolling: 'no',
        arrows: false,
        helpers: {
          overlay: {
            locked: false
          }
        }
      };

      $gallery.on('click', '.vimeography-thumbnail a', function(e){
        var $video = $(this);

        $('.vimeography-thumbnail').removeClass('active');
        $video.closest('.vimeography-thumbnail').addClass('active');

        var title = $video.attr('title');
        var downloads = {
          hd: $video.data('download-hd'),
          sd: $video.data('download-sd'),
          mobile: $video.data('download-mobile'),
          source: $video.data('download-source')
        };

        /* var plays = $video.attr('data-plays').toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");*/
        var promise = vimeography.utilities.get_video( $video.attr('href') );

        promise.done(function(video){
          /* This needs to be done so that the Vimeo API can interact with the player */
          video.html = vimeography.utilities.set_video_id(video.html);

          $template.find('.vimeography-video').html(video.html).end().fitVids();
          $template.find('.vimeography-title').html( title );
          $template.find('.vimeography-description').html($video.attr('data-description'));

          if (downloads.sd !== undefined) {
            var links = '';
            $.each(downloads, function(type, link){
              if (link !== undefined) {
                links = links + '<a href="' + link + '" download="' + title + '" title="Download ' + title + '">' + type.toUpperCase() + '</a> ';
              }
            });
            $template.find('.vimeography-download-links').html( links );
          }

          /*if ($video.attr('data-tags').length)
            $template.find('.vimeography-tags').html("Filed under " + $video.attr('data-tags'));*/

          opts.content = $template;
          opts.afterShow = function(){ $gallery.trigger('vimeography/video/ready'); };

          self == top ? $.fancybox(opts) : parent.jQuery.fancybox(opts);
        });

        e.preventDefault();
      });

      $gallery.on('vimeography/playlist/next', function(){
        $.fancybox.showLoading();
        var current_video_id = $('.vimeography-thumbnail.active').attr('id');
        /* Increment the last number (index) in the ID string */
        var next_video_id = current_video_id.replace(/[0-9]+(?!.*[0-9])/, function(match) {
            return parseInt(match, 10)+1;
        });

        $next_video = $('#' + next_video_id);

        if ($next_video.length) {
          $next_video.find('a').trigger('click');
        } else {
          $.fancybox.close();
          $.fancybox.hideLoading();
        }
      });

      $('body').on('click', '.vimeography-close', function(e){
        $.fancybox.close();
      });

      var mosaicflow_opts = {
        itemSelector: '.vimeography-thumbnail',
        minItemWidth: 300, /*Decrease this number if you want more columns, or increase if you want less.*/
        columnClass: 'vimeography-thumbnail-column'
      }

      $gallery.find('.vimeography-thumbnails').mosaicflow(mosaicflow_opts);

      $(window).bind("mosaicflow-layout", function(e){
        var $columns = $gallery.find('.vimeography-thumbnail-column');
        $columns.removeClass('last');
        var index = $columns.length - 1;
        $columns.eq(index).addClass('last');
      });


      /* Add paging methods if paging enabled */
      if (typeof timber.enable_paging != 'undefined') {

        /* Public methods */
        vimeography.pagination.set_pages();
        vimeography.pagination.set_paging_controls();

        $gallery.on('click', '.vimeography-paging', function(e){

          if ($(this).hasClass('vimeography-paging-disabled')) return false;

          $gallery.find('.vimeography-loader').spin('custom').animate({opacity: 1});

          var promise = vimeography.pagination.paginate($(this));

          promise.done(function(response){
            if (response.result == 'success') {
              $gallery.find('.vimeography-loader').animate({opacity: 0}, 400, function(){
                $(this).spin(false);
              });

              var container = $gallery.find('.vimeography-thumbnails');

              container.mosaicflow('empty');
              container.html(response.html).mosaicflow('init');
              $(window).trigger('resize');
              $gallery.find('.vimeography-page-number').html(response.page);
            }
          });

          e.preventDefault();
        });

      }
    });
  });

}( window.vimeography.timber = window.vimeography.timber || {}, jQuery ));
