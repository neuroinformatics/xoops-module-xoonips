<?php

// $Revision: 1.1.2.4 $
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
/* constant strings
 * $Revision: 1.1.2.4 $
 */

// _MD_<MODULENAME>_<STRINGNAME>

//metadata:junii type
/* id : text
00 : 研究成果,
01 : 研究成果—論文,
02 : 研究成果—論文以外,/
10 : 研究成果リスト,
11 : 研究成果リスト—逐次刊行物,
12 : 研究成果リスト—論文リスト,
13 : 研究成果リスト—プロジェクト関連情報,
14 : 研究成果リスト—講演会等,/
20 : 研究資源,
21 : 研究資源—データ,
22 : 研究資源—ソフトウェア,
23 : 研究資源—電子的辞書等,/
30 : 研究者情報,
31 : 研究者情報—個人のページ,
32 : 研究者情報—研究室トップページ,
33 : 研究者情報—研究者情報リスト,
34 : 研究者情報—研究者情報データベース,/
40 : 教育情報,
41 : 教育情報—講義情報リスト,
42 : 教育情報—電子教材リスト,/
50 : 図書館情報,
51 : 図書館情報—図書館・室トップページ,
52 : 図書館情報—図書館資料,/
60 : デジタルミュージアム,/
70 : 参考情報,
71 : 参考情報—データベース,
72 : 参考情報—文献目録・文献検索,
73 : 参考情報—リンク集・電子ジャーナル集,
74 : 参考情報—メーリングリスト,/
80 : 広報資料,
81 : 広報資料—機関トップページ,
82 : 広報資料—下部組織トップページ,
83 : 広報資料—機関広報資料
*/
define(
    '_MD_XOONIPS_METADATA_JUNII', '
研究成果,
研究成果—論文,
研究成果—論文以外,/
研究成果リスト,
研究成果リスト—逐次刊行物,
研究成果リスト—論文リスト,
研究成果リスト—プロジェクト関連情報,
研究成果リスト—講演会等,/
研究資源,
研究資源—データ,
研究資源—ソフトウェア,
研究資源—電子的辞書等,/
研究者情報,
研究者情報—個人のページ,
研究者情報—研究室トップページ,
研究者情報—研究者情報リスト,
研究者情報—研究者情報データベース,/
教育情報,
教育情報—講義情報リスト,
教育情報—電子教材リスト,/
図書館情報,
図書館情報—図書館・室トップページ,
図書館情報—図書館資料,/
デジタルミュージアム,/
参考情報,
参考情報—データベース,
参考情報—文献目録・文献検索,
参考情報—リンク集・電子ジャーナル集,
参考情報—メーリングリスト,/
広報資料,
広報資料—機関トップページ,
広報資料—下部組織トップページ,
広報資料—機関広報資料
'
);

// Metadata: JUNII2 type (will be fixed by 2006-10-13)
/* id : text
00 : Journal Article
01 : Thesis or Dissertation
02 : Departmental Bulletin Paper
03 : Conference Paper
04 : Book
05 : Technical Report
06 : Research Paper
07 : Preprint
10 : Presentation
11 : Article
12 : Learning Material
20 : Data or Dataset
30 : Software
 */
define(
    '_MD_XOONIPS_METADATA_JUNII2', '
Journal Article,
Thesis or Dissertation,
Departmental Bulletin Paper,
Conference Paper,
Book,
Technical Report,
Research Paper,
Preprint,/
Presentation,
Article,
Learning Material,/
Data or Dataset,/
Software
'
);
