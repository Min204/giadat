<?php

/**
 * @see XenForo_Model_Attachment
 */
class ThemeHouse_Thumbnails_Extend_XenForo_Model_Attachment extends XFCP_ThemeHouse_Thumbnails_Extend_XenForo_Model_Attachment
{
    /**
     * Get the first attachments (and limited data info) by the given content
     * IDs.
     *
     * @param string $contentType
     * @param array  $contentIds
     *
     * @return array Format: [content id] => info
     */
    public function getFirstAttachmentsByContentIds($contentType, array $contentIds)
    {
        return $this->fetchAllKeyed(
            '
			SELECT attachment.*,
				'.self::$dataColumns.'
			FROM xf_attachment AS attachment
			INNER JOIN xf_attachment_data AS data ON
				(data.data_id = attachment.data_id)
			WHERE attachment.content_type = ?
				AND attachment.content_id IN ('.$this->_getDb()
                ->quote($contentIds).')
                AND (data.filename LIKE \'%.png\' OR
                    data.filename LIKE \'%.jpg\' OR
                    data.filename LIKE \'%.jpeg\' OR
                    data.filename LIKE \'%.jpe\' OR
                    data.filename LIKE \'%.gif\')
			GROUP BY attachment.content_id
			ORDER BY attachment.content_id, attachment.attach_date
		', 'content_id', $contentType);
    }
}
