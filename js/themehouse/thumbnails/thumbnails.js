!function(c,e,f,g){XenForo.ThumbnailsForm=function(b){var a=b.data("thumbnailsurl");a?b.find(".ThumbnailsButton").click(function(c){XenForo.ajax(a,b.serialize(),function(a){if(XenForo.hasResponseError(a)||!XenForo.hasTemplateHtml(a))return!1;new XenForo.ExtLoader(a,function(a){var d=b.find(".ThumbnailsContainer").first();d.xfFadeOut(XenForo.speed.fast,function(){d.html(a.templateHtml).xfActivate()});d.xfFadeIn()})})}):console.warn("ThumbnailsForm has no data-thumbnailsurl: %o",b)};XenForo.Thumbnail=
function(b){b.click(function(a){a.preventDefault();c("#ctrl_thumbnail_url").val(function(a,c){return b.data("thumbnailurl")})})};XenForo.register("a.Thumbnail","XenForo.Thumbnail");XenForo.register("form.Thumbnails","XenForo.ThumbnailsForm")}(jQuery,this,document);
