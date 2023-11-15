<?php

class ThemeHouse_Thumbnails_Listener_TemplateModification extends ThemeHouse_Listener_TemplateModification
{
    public static function threadListItem(array $matches)
    {
        $modification = new self($matches[0]);

        return $modification->_threadListItem();
    }

    protected function _threadListItem()
    {
        $pattern = '#<div class="listBlock posterAvatar">\s*<span class="avatarContainer">.*</span>\s*</div>#Us';
        // $replacement = '<xen:if is="!{$thread.thumbnail} || ({$xenOptions.th_showInForums_thumbnails._type} == \'_some\' && !{$xenOptions.th_showInForums_thumbnails.{$forum.node_id}})">
            // ${0}
            // <xen:else />
            // <xen:include template="th_forum_list_thumbnail_thumbnails" />
            // </xen:if>';
		$replacement = '
			<xen:if is="{$thread.forum}"><xen:set var="$nodeId">{$thread.forum.node_id}</xen:set></xen:if>
			<xen:if is="{$forum}"><xen:set var="$nodeId">{$forum.node_id}</xen:set></xen:if>

			<xen:if is="!{$thread.thumbnail} || ({$xenOptions.th_showInForums_thumbnails._type} == \'_some\' && !{$xenOptions.th_showInForums_thumbnails.{$nodeId}})">
				${0}
            <xen:else />
				<xen:include template="th_forum_list_thumbnail_thumbnails" />
            </xen:if>
		';

        $this->_patternReplace($pattern, $replacement);

        return $this->_contents;
    }

    public static function wfWidgetThreadsThreadSidebar(array $matches)
    {
        $modification = new self($matches[0]);

        return $modification->_wfWidgetThreadsThreadSidebar();
    }

    protected function _wfWidgetThreadsThreadSidebar()
    {
        $pattern = '#<xen:avatar user="\$thread" size="s" img="true" />#Us';
        $replacement = '<xen:if is="!{$thread.thumbnail} || !{$xenOptions.th_thumbsWidgetFramework_thumbnails}">
            ${0}
            <xen:else />
            <xen:include template="th_widget_threads_thumbnail_thumbnails" />
            </xen:if>';
        $this->_patternReplace($pattern, $replacement);

        return $this->_contents;
    }
}
