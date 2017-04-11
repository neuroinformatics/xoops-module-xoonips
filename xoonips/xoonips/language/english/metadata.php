<?php

// $Revision: 1.4.8.3 $
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
 * $Revision: 1.4.8.3 $
 */

// _MD_<MODULENAME>_<STRINGNAME>

//metadata:junii type
/* id : text
00 : Kenkyu seika,
01 : Kenkyu seika - Ronbun,
02 : Kenkyu seika - Ronbun igai,/
10 : Kenkyu seika list,
11 : Kenkyu seika list - Chikuji kankobutu,
12 : Kenkyu seika list - Ronbun list,
13 : Kenkyu seika list - Project kanren joho,
14 : Kenkyu seika list - Koenkai tou,/
20 : Kenkyu shigen,
21 : Kenkyu shigen - Data,
22 : Kenkyu shigen - Software,
23 : Kenkyu shigen - Denshiteki jisho tou,/
30 : Kenkyusha joho,
31 : Kenkyusha joho - Kojin no page,
32 : Kenkyusha joho - Kenkyushitsu top page,
33 : Kenkyusha joho - Kenkyusha joho list,
34 : Kenkyusha joho - Kenkyusha joho database,/
40 : Kyoiku joho,
41 : Kyoiku joho - Kogi joho list,
42 : Kyoiku joho - Denshi kyozai list,/
50 : Toshokan joho,
51 : Toshokan joho - Toshokan / shitsu top page,
52 : Toshokan joho - Toshokan shiryo,/
60 : Digital Museum,/
70 : Sanko joho,
71 : Sanko joho - Database,
72 : Sanko joho - Bunken mokuroku / Bunken kensaku,
73 : Sanko joho - Link shu / Denshi journal shu,
74 : Sanko joho - Mailing list,/
80 : Koho shiryo,
81 : Koho shiryo - Kikan top page,
82 : Koho shiryo - Kabu soshiki top page,
83 : Koho shiryo - Kikan Koho shiryo
*/
define('_MD_XOONIPS_METADATA_JUNII', '
&#30740;&#31350;&#25104;&#26524;,
&#30740;&#31350;&#25104;&#26524;&#8213;&#35542;&#25991;,
&#30740;&#31350;&#25104;&#26524;&#8213;&#35542;&#25991;&#20197;&#22806;,/
&#30740;&#31350;&#25104;&#26524;&#12522;&#12473;&#12488;,
&#30740;&#31350;&#25104;&#26524;&#12522;&#12473;&#12488;&#8213;&#36880;&#27425;&#1322;&#34892;&#29289;,
&#30740;&#31350;&#25104;&#26524;&#12522;&#12473;&#12488;&#8213;&#35542;&#25991;&#12522;&#12473;&#12488;,
&#30740;&#31350;&#25104;&#26524;&#12522;&#12473;&#12488;&#8213;&#12503;&#12525;&#12472;&#12455;&#12463;&#12488;&#38306;&#36899;&#24773;&#22577;,
&#30740;&#31350;&#25104;&#26524;&#12522;&#12473;&#12488;&#8213;&#35611;&#28436;&#20250;&#31561;,/
&#30740;&#31350;&#36039;&#28304;,
&#30740;&#31350;&#36039;&#28304;&#8213;&#12487;&#12540;&#12479;,
&#30740;&#31350;&#36039;&#28304;&#8213;&#12477;&#12501;&#12488;&#12454;&#12455;&#12450;,
&#30740;&#31350;&#36039;&#28304;&#8213;&#38651;&#23376;&#30340;&#36766;&#26360;&#31561;,/
&#30740;&#31350;&#2053;&#24773;&#22577;,
&#30740;&#31350;&#2053;&#24773;&#22577;&#8213;&#1291;&#20154;&#12398;&#12506;&#12540;&#12472;,
&#30740;&#31350;&#2053;&#24773;&#22577;&#8213;&#30740;&#31350;&#23460;&#12488;&#12483;&#12503;&#12506;&#12540;&#12472;,
&#30740;&#31350;&#2053;&#24773;&#22577;&#8213;&#30740;&#31350;&#2053;&#24773;&#22577;&#12522;&#12473;&#12488;,
&#30740;&#31350;&#2053;&#24773;&#22577;&#8213;&#30740;&#31350;&#2053;&#24773;&#22577;&#12487;&#12540;&#12479;&#12505;&#12540;&#12473;,/
&#25945;&#32946;&#24773;&#22577;,
&#25945;&#32946;&#24773;&#22577;&#8213;&#35611;&#32681;&#24773;&#22577;&#12522;&#12473;&#12488;,
&#25945;&#32946;&#24773;&#22577;&#8213;&#38651;&#23376;&#25945;&#26448;&#12522;&#12473;&#12488;,/
&#22259;&#26360;&#39208;&#24773;&#22577;,
&#22259;&#26360;&#39208;&#24773;&#22577;&#8213;&#22259;&#26360;&#39208;&#12539;&#23460;&#12488;&#12483;&#12503;&#12506;&#12540;&#12472;,
&#22259;&#26360;&#39208;&#24773;&#22577;&#8213;&#22259;&#26360;&#39208;&#36039;&#26009;,/
&#12487;&#12472;&#12479;&#12523;&#12511;&#12517;&#12540;&#12472;&#12450;&#12512;,/
&#21442;&#32771;&#24773;&#22577;,
&#21442;&#32771;&#24773;&#22577;&#8213;&#12487;&#12540;&#12479;&#12505;&#12540;&#12473;,
&#21442;&#32771;&#24773;&#22577;&#8213;&#25991;&#29486;&#30446;&#37682;&#12539;&#25991;&#29486;&#26908;&#32034;,
&#21442;&#32771;&#24773;&#22577;&#8213;&#12522;&#12531;&#12463;&#38598;&#12539;&#38651;&#23376;&#12472;&#12515;&#12540;&#12490;&#12523;&#38598;,
&#21442;&#32771;&#24773;&#22577;&#8213;&#12513;&#12540;&#12522;&#12531;&#12464;&#12522;&#12473;&#12488;,/
&#24195;&#22577;&#36039;&#26009;,
&#24195;&#22577;&#36039;&#26009;&#8213;&#27231;&#38306;&#12488;&#12483;&#12503;&#12506;&#12540;&#12472;,
&#24195;&#22577;&#36039;&#26009;&#8213;&#1259;&#37096;&#32068;&#32340;&#12488;&#12483;&#12503;&#12506;&#12540;&#12472;,
&#24195;&#22577;&#36039;&#26009;&#8213;&#27231;&#38306;&#24195;&#22577;&#36039;&#26009;,
');

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
define('_MD_XOONIPS_METADATA_JUNII2', '
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
');
