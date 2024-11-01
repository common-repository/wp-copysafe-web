//  WP Copysafe Web 
//  Copyright (c) 1998-2022 ArtistScope. All Rights Reserved.
//  www.artistscope.com
//
// The Copysafe Web Plugin is supported across all Windows OS since XP.
//
// Special JS version for Wordpress

// Debugging outputs the generated html into a textbox instead of rendering
// option has been moved to wp-copysafe-web.php

// REDIRECTS

var m_szLocation = document.location.href.replace(/&/g,'%26');	
var m_szDownloadNo = wpcsw_plugin_url + "download_no.html";
var m_szDownload = wpcsw_plugin_url + "download.html";
var m_szDownloadUpdate = wpcsw_plugin_url + "download-update.html";


//===========================
//   DO NOT EDIT BELOW 
//===========================

var m_szAgent = navigator.userAgent.toLowerCase();
var m_szBrowserName = navigator.appName.toLowerCase();
var m_szPlatform = navigator.platform.toLowerCase();
var m_bNetscape = false;
var m_bMicrosoft = false;
var m_szPlugin = "";

var m_bWin64 = ((m_szPlatform == "win64") || (m_szPlatform.indexOf("win64")!=-1) || (m_szAgent.indexOf("win64")!=-1));
var m_bWin32 = ((m_szPlatform == "win32") || (m_szPlatform.indexOf("win32")!=-1));
var m_bWindows = (m_szAgent.indexOf("windows nt")!=-1);

var m_bASPS = ((m_szAgent.indexOf("artisreader")!=-1) && (m_bpASPS));
var m_bFirefox = ((m_szAgent.indexOf("firefox") != -1) && (m_bpFx));
var m_bChrome = ((m_szAgent.indexOf("chrome") != -1) && !(window.chrome && chrome.webstore && chrome.webstore.install) && (m_bpChrome));

var m_bNetscape = ((m_bASPS) || (m_bChrome) || (m_bFirefox));

if((m_bWindows) && (m_bNetscape) > 0)
{
	if( !m_bASPS && !m_bpDebugging ){
		window.location=unescape(m_szDownload);
		document.MM_returnValue=false;
	}
	else{
		m_szPlugin = "DLL";
	}
}
else if( !m_bWindows )
{
	window.location=unescape(m_szDownloadNo);
	document.MM_returnValue=false;
}
else
{
	window.location=unescape(m_szDownload);
	document.MM_returnValue=false;
}

function bool2String(bValue)
{
	if (bValue == true) {
		return "1";
	}
	else {
		return "0";
	}
}

function paramValue(szValue, szDefault)
{
	if (szValue.toString().length > 0) {
		return szValue;
	}
	else {
		return szDefault;
	}
}

function expandNumber(nValue, nLength)
{
    var szValue = nValue.toString();
    while(szValue.length < nLength)
        szValue = "0" + szValue;
    return szValue;
}

// The copysafe-insert functions

function insertCopysafeWeb(szImageName, szcWidth, szcHeight)
{
    // Extract the image width and height from the image name (example name: zulu580_0580_0386_C.class)

    var nIndex = szImageName.lastIndexOf('_C.');
    if (nIndex == -1 && !m_bpDebugging)
    {
        // Strange filename that doesn't conform to the copysafe standard. Can't render it.
        return;
    }

	if (!szcWidth) {
		var szWidth = szImageName.substring(nIndex - 9, nIndex - 5);
	} 
	else {
		var szWidth = szcWidth;
	}
	if (!szcHeight) {
		var szHeight = szImageName.substring(nIndex - 4, nIndex);
	} 
	else {
		var szHeight = szcHeight;
	}

    var nWidth = szWidth * 1;
    var nHeight = szHeight * 1;

    // Expand width and height to allow for border

    var nBorder = m_szDefaultBorder * 1;
    nWidth = nWidth + (nBorder * 2);
    nHeight = nHeight + (nBorder * 2);

    insertCopysafeImage(nWidth, nHeight, "", "", nBorder, "", "", "", [szImageName]);

}

function insertCopysafeImage(nWidth, nHeight, szTextColor, szBorderColor, nBorder, szLoading, szLink, szTargetFrame, arFrames)
{
	if (m_bpDebugging == true)
	{ 
        document.writeln("<textarea rows='27' cols='80'>"); 
	} 

    var szObjectInsert = "";
    
    if (m_szPlugin == "DLL")
    {
    	szObjectInsert = "type='application/x-artistscope-firefox5' codebase='" + wpcsw_plugin_url + "download-update.html' ";
        document.writeln("<ob" + "ject " + szObjectInsert + " width='" + nWidth + "' height='" + nHeight + "'>");

		document.writeln("<param name='KeySafe' value='" + bool2String(m_bpKeySafe) + "' />");
		document.writeln("<param name='CaptureSafe' value='" + bool2String(m_bpCaptureSafe) + "' />");
		document.writeln("<param name='MenuSafe' value='" + bool2String(m_bpMenuSafe) + "' />");
		document.writeln("<param name='RemoteSafe' value='" + bool2String(m_bpRemoteSafe) + "' />");
		
		document.writeln("<param name='Style' value='ImageLink' />");
		document.writeln("<param name='TextColor' value='" + paramValue(szTextColor, m_szDefaultTextColor) + "' />");
		document.writeln("<param name='BorderColor' value='" + paramValue(szBorderColor, m_szDefaultBorderColor) + "' />");
		document.writeln("<param name='Border' value='" + paramValue(nBorder, m_szDefaultBorder) + "' />");
		document.writeln("<param name='Loading' value='" + paramValue(szLoading, m_szDefaultLoading) + "' />");
		document.writeln("<param name='Label' value='' />");
		document.writeln("<param name='Link' value='" + paramValue(szLink, m_szDefaultLink) + "' />");
		document.writeln("<param name='TargetFrame' value='" + paramValue(szTargetFrame, m_szDefaultTargetFrame) + "' />");
		document.writeln("<param name='Message' value='' />");   
		document.writeln("<param name='FrameDelay' value='2000' />");
		document.writeln("<param name='FrameCount' value='1' />");
		document.writeln("<param name='Frame000' value='" + m_szImageFolder + m_szClassName + "' />");

		document.writeln("</ob" + "ject />"); 

		if (m_bpDebugging == true)
		{
			document.writeln("</textarea />");
		}
    }
}