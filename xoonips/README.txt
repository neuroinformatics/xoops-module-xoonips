xoonips 3.49 : Neuroinformatics Base Platform System 

Copyright (C) 2005-2016 RIKEN, Japan All rights reserved.

Managed by Neuroinformatics Japan Center, RIKEN Brain Science Institute.

DESCRIPTION:
  XooNIps is Neuroinformatics Base Platform System based on the XOOPS.

LICENSE:
  This software was provided under GPL (The GNU General Public License)
  version 2. See COPYING.txt file for more detail.

Change Logs:
  see ChangeLogs.txt, ChangeLog-ja.txt(in Japanese)

Directories Layout:
  |- README.txt          This file
  |- COPYING.txt         License information
  |- ChangeLog.txt       Change log information in English
  |- ChangeLog-ja.txt    Change log information in Japanese
  |- xoonips             XooNIps module
  |- itemtypes           Item type modules for the XooNIps
  |   |- xnpbook         Book item type module
  |   |- xnpconference   Conference item type module
  |   |- xnpdata         Data item type module
  |   |- xnpfiles        File item type module
  |   |- xnpmemo         Memo item type module
  |   |- xnpmodel        Model item type module
  |   |- xnppaper        Paper item type module
  |   |- xnppresentation Presentation item type module
  |   |- xnpsimulator    Simulator item type module
  |   |- xnpstimulus     Stimulus item type module
  |   |- xnptool         Tool item type module
  |   -- xnpurl          Url item type module
  |- themes              XooNIps project designed XOOPS themes
  |   |- XooNIps-III     Nice xoonips theme
  |   -- xoonips         Simple xoonips theme
  |- contrib             Information of the third vendor provided modules and 
  |                      libraries (see contrib/LIST.txt for more detail)
  -- preload             Sample preloads

Install:
 1) put 'xoonips' directory to the XOOPS modules directory.
 2) push install button of xoonips module on the XOOPS module administration
    page.
 3) choose item type modules from under 'itemtypes' directory.
 4) put your chosen item type modules to the XOOPS modules directory.
 5) push install button of item types modules on the XOOPS module
    administration page respectively.
 6) put XooNIps project designed XOOPS themes to the XOOPS themes directory,
    if you want to use.

Setup:
 1) configure XooNIps system settings on the XooNIps administration page.
 2) decide site policies and configure these policies on the XooNIps
    administration page.

Upgrade:
 0) !!!! VERY IMPORTANT !!!!
    check your using XooNIps version. the upgrade supported versions of
    the XooNIps are 2.00 or 3.19 or 3.24 only. if you don't use these 
    supported versions then you have to upgrade once to version 3.24 before
    to use this version.
 1) replace 'xoonips' module and each item type modules on your XOOPS
    modules directory.
 2) push upgrade button of XooNIps module on the XOOPS module administration
    page.
 3) !!!! IMPORTANT !!!!
    (This is only if it's updated xoonips module before version 3.40)
    install Binder item type module on the XOOPS module instration page.
    at this moment, the installer of Binder item type module will move
    XooNIps module's managed data to own module.
 4) push upgrade button of each item type modules on the XOOPS module
    administration page respectively.
    page respectively.
 5) after upgrade 'xoonips' module, rescan/update all attachement file
    informations and search indexes on administration page of XooNIps
    module: 'Maintanance >> File Search'.

Acknowledgments:
  * The XooNIps project would like to thank KEIO University, who have made
    significant contributions to the development and implementation of the
    XooNIps.
