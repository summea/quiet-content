<?php

class Announcement extends AppModel
{

    var $name = 'Announcement';

    var $belongsTo = array(
        'AnnouncementCategory',
        'User' => array(
            'foreignKey' => 'id',
            'fields' => array(
                'id'
            )
        )
    );

    var $hasOne = array(
        'Status' => array(
            'foreignKey' => 'id',
            'type' => 'LEFT',
            'fields' => ' '
        )
    );

    var $hasMany = array(
        'AnnouncementImage' => array(
            'foreignKey' => 'announcement_id',
            'type' => 'INNER',
            'order' => 'AnnouncementImage.weight'
        ),
        'AnnouncementAttachment' => array(
            'foreignKey' => 'announcement_id',
            'type' => 'INNER',
            'order' => 'AnnouncementAttachment.weight'
        )
    );

    var $actsAs = array(
        'Containable'
    );

    function afterSave()
    {
        $homepage_cache_filename = Configure::read('homepage_cache_filename');
        
        if (is_file(CACHE . 'views' . DS . $homepage_cache_filename))
            unlink(CACHE . 'views' . DS . $homepage_cache_filename); // sweep away old homepage
    }

    function afterDelete()
    {
        $homepage_cache_filename = Configure::read('homepage_cache_filename');
        
        if (is_file(CACHE . 'views' . DS . $homepage_cache_filename))
            unlink(CACHE . 'views' . DS . $homepage_cache_filename); // sweep away old homepage
    }

    function list_recent_announcements($limit, $announcement_type = null)
    {
        if ($announcement_type) {
            return $this->find('all', array(
                'conditions' => array(
                    'Announcement.announcement_type' => $announcement_type,
                    'Announcement.status_id' => '1'
                ),
                'order' => 'Announcement.id DESC',
                'limit' => $limit
            ));
        } else {
            return $this->find('all', array(
                'conditions' => array(
                    'Announcement.status_id' => '1'
                ),
                'order' => 'Announcement.id DESC',
                'limit' => $limit
            ));
        }
    }

    function list_featured_announcements($limit, $announcement_type = null)
    {
        if ($announcement_type) {
            return $this->find('all', array(
                'conditions' => array(
                    'Announcement.announcement_type' => $announcement_type,
                    'Announcement.status_id' => '1'
                ),
                'order' => 'Announcement.modified DESC',
                'limit' => $limit
            ));
        } else {
            return $this->find('all', array(
                'conditions' => array(
                    'Announcement.status_id' => '1'
                ),
                'order' => 'Announcement.modified DESC',
                'limit' => $limit
            ));
        }
    }

    function list_recent_calendar_events($limit)
    {
        return $this->find('all', array(
            'conditions' => 'announcement_type = 1',
            'order' => 'Announcement.id DESC',
            'limit' => $limit
        ));
    }

    function resize_image($filename, $width, $height)
    {
        list ($width_orig, $height_orig, $file_type) = getimagesize($filename);
        
        if (($width_orig > $width) || ($height_orig > $height)) {
            if ($width && ($width_orig < $height_orig)) {
                $width = ($height / $height_orig) * $width_orig;
            } else {
                $height = ($width / $width_orig) * $height_orig;
            }
            
            ob_start();
            
            if ($file_type == 1)
                $src_img = imagecreatefromgif($filename);
            else 
                if ($file_type == 2)
                    $src_img = imagecreatefromjpeg($filename);
                else 
                    if ($file_type == 3)
                        $src_img = imagecreatefrompng($filename);
                    else
                        $src_img = imagecreatefromjpeg($filename);
            
            $dst_img = imagecreatetruecolor($width, $height);
            imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
            
            imagejpeg($dst_img, $filename, 75);
            
            imagedestroy($dst_img);
            imagedestroy($src_img);
            
            ob_end_clean();
        }
    }
}
?>
