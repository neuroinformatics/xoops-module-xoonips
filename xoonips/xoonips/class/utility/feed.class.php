<?php

// $Revision: 1.1.2.10 $
// ------------------------------------------------------------------------- //
//  XooNIps - Neuroinformatics Base Platform System                          //
//  Copyright (C) 2005-2011 RIKEN, Japan All rights reserved.                //
//  http://xoonips.sourceforge.jp/                                           //
// ------------------------------------------------------------------------- //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------- //
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

define('XOONIPS_FEED_DATE_RDF', 'Y-m-d\\TH:i:s+00:00');
define('XOONIPS_FEED_DATE_RSS', 'D, d M Y H:i:s T');
define('XOONIPS_FEED_DATE_ATOM', 'Y-m-d\\TH:i:s\\Z');

/**
 * feed (RDF,RSS,Atom) generation class.
 *
 * @copyright copyright &copy; 2005-2008 RIKEN Japan
 */
class XooNIpsUtilityFeed extends XooNIpsUtility
{
    /**
   * text utility handler instance.
   *
   * @var object class instance of XooNIpsUtilityText
   */
  public $textutil;

  /**
   * site title.
   *
   * @var string site title
   */
  public $site_title;

  /**
   * site url.
   *
   * @var string site url
   */
  public $site_url;

  /**
   * site language code.
   *
   * @var string site language code
   */
  public $site_language;

  /**
   * site author name.
   *
   * @var string site author name
   */
  public $site_author;

  /**
   * site description.
   *
   * @var string site description
   */
  public $site_description;

  /**
   * site copyright.
   *
   * @var string site copyright
   */
  public $site_copyright;

  /**
   * feed items.
   *
   * @var array list of feed entries
   */
  public $items;

  /**
   * constructor.
   */
  public function XooNIpsUtilityFeed()
  {
      $myxoopsConfig = &xoonips_get_xoops_configs(XOOPS_CONF);
      $myxoopsConfigMetaFooter = &xoonips_get_xoops_configs(XOOPS_CONF_METAFOOTER);
      $this->textutil = &xoonips_getutility('text');
      $this->site_title = $this->textutil->xml_special_chars($myxoopsConfig['sitename']);
      $this->site_url = XOOPS_URL.'/';
      $this->site_language = _LANGCODE;
      $this->site_author = $this->textutil->xml_special_chars($myxoopsConfigMetaFooter['meta_author']);
      $this->site_description = $this->textutil->xml_special_chars($myxoopsConfigMetaFooter['meta_description']);
      $this->site_copyright = $this->textutil->xml_special_chars($myxoopsConfigMetaFooter['meta_copyright']);
      $this->items = array();
  }

  /**
   * add item.
   *
   * @param string $category    category
   * @param string $title       title
   * @param string $description description
   * @param string $link        link url
   * @param int    $timestamp   timestamp
   */
  public function addItem($category, $title, $description, $link, $timestamp)
  {
      $this->items[] = array(
      'category' => $this->textutil->xml_special_chars($category),
      'title' => $this->textutil->xml_special_chars($title),
      'description' => $this->textutil->xml_special_chars($description),
      'link' => $link,
      'timestamp' => $timestamp,
    );
  }

  /**
   * render feeds.
   *
   * @param string $type     feed type belows:
   *                         'rdf'  : RSS 1.0
   *                         'rss'  : RSS 2.0
   *                         'atom' : Atom 1.0
   * @param string $feed_url feeder url
   */
  public function render($type, $feed_url)
  {
      // sort by timestamp
    if (count($this->items) > 1) {
        usort($this->items, array('XooNIpsUtilityFeed', '_compare_items'));
    }
      switch ($type) {
    case 'rdf':
      $this->_renderRDF($feed_url);
      break;
    case 'rss':
      $this->_renderRSS($feed_url);
      break;
    case 'atom':
      $this->_renderATOM($feed_url);
      break;
    }
      exit();
  }

  /**
   * compare items for item sorting.
   *
   * @param array $a item A
   * @param array $b item B
   *
   * @return int result
   */
  public function _compare_items($a, $b)
  {
      if ($a['timestamp'] == $b['timestamp']) {
          return 0;
      }

      return($a['timestamp'] < $b['timestamp']) ? 1 : -1;
  }

  /**
   * output RDF feeds (RSS 1.0 : RDF Site Summary).
   *
   * @param string $feed_url feed url
   */
  public function _renderRDF($feed_url)
  {
      // header
    header('Content-type: application/rss+xml');
      echo '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
      echo '<rdf:RDF';
      echo ' xmlns="http://purl.org/rss/1.0/"';
      echo ' xmlns:dc="http://purl.org/dc/elements/1.1/"';
      echo ' xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"';
      echo '>'."\n";
      echo '<channel rdf:about="'.$this->site_url.'">'."\n";
      echo '<title>'.$this->site_title.'</title>'."\n";
      echo '<link>'.$this->site_url.'</link>'."\n";
      echo '<description>'.$this->site_description.'</description>'."\n";
      echo '<dc:language>'.$this->site_language.'</dc:language>'."\n";
      echo '<dc:rights>'.$this->site_copyright.'</dc:rights>'."\n";
      echo '<dc:date>'.gmdate(XOONIPS_FEED_DATE_RDF, time()).'</dc:date>'."\n";
      echo '<dc:publisher>'.$this->site_author.'</dc:publisher>'."\n";
      echo '<dc:creator>'.$this->site_author.'</dc:creator>'."\n";
      echo '<items>'."\n";
      echo '<rdf:Seq>'."\n";
      foreach ($this->items as $item) {
          echo '<rdf:li rdf:resource="'.$item['link'].'"/>'."\n";
      }
      echo '</rdf:Seq>'."\n";
      echo '</items>'."\n";
      echo '</channel>'."\n";
    // entries
    foreach ($this->items as $item) {
        $date = gmdate(XOONIPS_FEED_DATE_RDF, $item['timestamp']);
        echo '<item rdf:about="'.$item['link'].'">'."\n";
        echo '<title>'.$item['title'].'</title>'."\n";
        echo '<link>'.$item['link'].'</link>'."\n";
        echo '<description>'.$item['description'].'</description>'."\n";
        echo '<dc:subject>'.$item['category'].'</dc:subject>'."\n";
        echo '<dc:date>'.$date.'</dc:date>'."\n";
        echo '</item>'."\n";
    }
    // footer
    echo '</rdf:RDF>'."\n";
  }

  /**
   * output RSS feeds (RSS 2.0 : Really Simple Summary).
   *
   * @param string $feed_url feed url
   */
  public function _renderRSS($feed_url)
  {
      // header
    header('Content-type: application/rss+xml');
      echo '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
      echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'."\n";
      echo '<channel>'."\n";
      echo '<title>'.$this->site_title.'</title>'."\n";
      echo '<link>'.$this->site_url.'</link>'."\n";
      echo '<description>'.$this->site_description.'</description>'."\n";
      echo '<language>'.$this->site_language.'</language>'."\n";
      echo '<atom:link rel="self" type="application/rss+xml" href="'.$feed_url.'"/>'."\n";
    // entries
    foreach ($this->items as $item) {
        $date = gmdate(XOONIPS_FEED_DATE_RSS, $item['timestamp']);
        echo '<item>'."\n";
        echo '<title>'.$item['title'].'</title>'."\n";
        echo '<link>'.$item['link'].'</link>'."\n";
        echo '<pubDate>'.$date.'</pubDate>'."\n";
        echo '<guid>'.$item['link'].'</guid>'."\n";
        echo '<description>'.$item['description'].'</description>'."\n";
        echo '<category>'.$item['category'].'</category>'."\n";
        echo '</item>'."\n";
    }
    // footer
    echo '</channel>'."\n";
      echo '</rss>'."\n";
  }

  /**
   * output Atom feeds (Atom Syndication Format 1.0).
   *
   * @param string $feed_url
   */
  public function _renderATOM($feed_url)
  {
      // header
    header('Content-type: application/atom+xml');
      echo '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
      echo '<feed';
      echo ' xmlns="http://www.w3.org/2005/Atom"';
      echo ' xml:lang="'.$this->site_language.'"';
      echo ' xmlns:dc="http://purl.org/dc/elements/1.1/"';
      echo '>'."\n";
      echo '<id>'.$feed_url.'</id>'."\n";
      echo '<title>'.$this->site_title.'</title>'."\n";
      echo '<subtitle type="html">'.$this->site_description.'</subtitle>'."\n";
      echo '<updated>'.gmdate(XOONIPS_FEED_DATE_ATOM, time()).'</updated>'."\n";
      echo '<link rel="alternate" type="text/html" hreflang="'.$this->site_language.'" href="'.$this->site_url.'"/>'."\n";
      echo '<link rel="self" type="application/atom+xml" href="'.$feed_url.'"/>'."\n";
      echo '<author>'."\n";
      echo '<name>'.$this->site_author.'</name>'."\n";
      echo '</author>'."\n";
    // entries
    $domain = 'localhost';
      $subdir = '';
      if (preg_match('/^(http|https):\\/\\/([^\\/]+)(.*)?$/', XOOPS_URL, $matches)) {
          $domain = $matches[2];
          $subdir = $matches[3];
      }
      $date_count = array();
      foreach ($this->items as $item) {
          $date = gmdate(XOONIPS_FEED_DATE_ATOM, $item['timestamp']);
      // generate uniq id
      $id_date = gmdate('Y-m-d', $item['timestamp']);
          $id_path = $subdir.str_replace(XOOPS_URL, '', $item['link']);
          $date_count[$date] = isset($date_count[$date]) ? $date_count[$date] + 1 : 0;
          $date = sprintf('%s.%02dZ', substr($date, 0, strlen($date) - 1), $date_count[$date]);
          echo '<entry>'."\n";
          echo '<id>tag:'.$domain.','.$id_date.':'.$id_path.','.$item['timestamp'].'</id>'."\n";
          echo '<title>'.$item['title'].'</title>'."\n";
          echo '<link rel="alternate" type="text/html" href="'.$item['link'].'"/>'."\n";
          echo '<updated>'.$date.'</updated>'."\n";
          if ($item['description'] == '') {
              $item['description'] = '(empty)';
          }
          echo '<summary>'.$item['description'].'</summary>'."\n";
          echo '<dc:subject>'.$item['category'].'</dc:subject>'."\n";
          echo '</entry>'."\n";
      }
    // footer
    echo '</feed>'."\n";
  }
}
