/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
	XenForo.ThumbnailsForm = function($form)
	{
		var thumbnailsUrl = $form.data('thumbnailsurl');
		if (!thumbnailsUrl)
		{
			console.warn('ThumbnailsForm has no data-thumbnailsurl: %o', $form);
			return;
		}

		$form.find('.ThumbnailsButton').click(function(e)
		{
			XenForo.ajax(thumbnailsUrl, $form.serialize(), function(ajaxData)
			{
				if (XenForo.hasResponseError(ajaxData) || !XenForo.hasTemplateHtml(ajaxData))
				{
					return false;
				}

				new XenForo.ExtLoader(ajaxData, function(ajaxData)
				{
					var $thumbnails = $form.find('.ThumbnailsContainer').first();
					$thumbnails.xfFadeOut(XenForo.speed.fast, function() {
						$thumbnails.html(ajaxData.templateHtml).xfActivate();
					});

					$thumbnails.xfFadeIn();
				});
			});
		});
	};
	
	XenForo.Thumbnail = function($link)
	{
		$link.click(function(e)
		{
			e.preventDefault();

			$("#ctrl_thumbnail_url").val(function( index, value ) {
				  return ($link.data("thumbnailurl"));
			}); 
			
		});
	};

	// Handle accounts links
    XenForo.register('a.Thumbnail', 'XenForo.Thumbnail');
	
	// Register form previewer
	XenForo.register('form.Thumbnails', 'XenForo.ThumbnailsForm');
	
	
}
(jQuery, this, document);