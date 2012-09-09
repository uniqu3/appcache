<?php
#-------------------------------------------------------------------------
# Plugin: AppCache - is a plugin for dynamic creation of cache manifest file
# Version: 0.1-alpha 
# Author: Goran Ilic ja@ich-mach-das.at
# Web: www.ich-mach-das.at
#
#-------------------------------------------------------------------------
# NOTICE:
# To make your webapp work offline you will need an .htaccess to set mimetype of manifest file
# htaccess:
#
# AddType text/cache-manifest manifest
# 
# and specify manifest attribute in your HTML5 declaration
# <!DOCTYPE html> 
# <html manifest="{appcache}"> 
#
#-------------------------------------------------------------------------
# CMS - CMS Made Simple is (c) 2007 by Ted Kulp (wishy@cmsmadesimple.org)
# This project's homepage is: http://www.cmsmadesimple.org
#
#-------------------------------------------------------------------------
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
# Or read it online: http://www.gnu.org/licenses/licenses.html#GPL
#
#-------------------------------------------------------------------------
#
# TODO:
# - find common event in EventManager, when is manifest file being updated (content has changed, files uploaded, template changed etc.)
# - NETWORK: easy way to define network stuff, like exclude pages by alias etc.
# - FALLBACK: how to specify fallbacks? Define page alias? Would that work or generate html file or something?
# - define exclude_files and exclude_folders param, like files or folders to exclude from cache
# - define include_folders and include_filetype param, for example specifying comma separated list of folders to scan, files to include like xml,js etc.
# - should scan folders recursively? or control with a param like recursive=1
# - define actions, like action='init' which goes in <html> tag and returns filename, then action='update' or action='js' which would output js to check if appcache needs update in browser?
#

function smarty_function_appcache($params, &$smarty)
{
    // get cmsms config
    $config = cmsms()->GetConfig();
    
    // params
    $folders      = isset($params['include_folders']) ? $params['include_folders'] : 'uploads/images';
    // vars
    $hash         = '';
    $cache_list   = array();
    $network_list = array();
    // scan specified directories
    // TODO find a way for a user friendly param, check recursively or use RecursiveDirectoryIterator??
    // TODO move this to a function like readResource($resource = array()) ??
    $paths        = array();
    foreach (explode(',', $folders) as $folder) {
        $paths[] = './' . $folder . '/';
    }
    
    foreach ($paths as $path) {
        $files = scandir($path);
        foreach ($files as $file) {
            if (preg_match('/\.(jpg|png|jpeg|gif|css|js|pdf)$/i', $file)) {
                $cache_list[] = $path . $file;
                $hash .= md5_file($file);
            }
            // put php files to network list
            if (preg_match('/.php$/', $file)) {
                $network_list[] = $path . $file;
                $hash .= md5_file($file);
            }
        }
    }
    
    // TODO move this to a function like creatManifest() ??
    // create cache file
    $manifest_file = 'cache.manifest';
    $write = fopen($manifest_file, 'w') or die('Error, could not create manifest.');
    $cache .= "CACHE MANIFEST\n# " . date('Y-m-d') . "\n\n# Explicitly cached 'master entries'.\nCACHE:\n";
    foreach ($cache_list as $file_path) {
        $cache .= $file_path . "\n";
    }
    // TODO network needs param as well, what do we exclude and what not? Almost better doing module that lists pages, folders and other stuff then check/uncheck options?
    $cache .= "\n\n# Resources that require the user to be online.\nNETWORK:\n";
    $cache .= "*\n";
    foreach ($network_list as $file_path) {
        $cache .= $file_path . "\n";
    }
    // TODO needs param to, what happens as fallback, like specify a html file or??
    $cache .= "# Fallback sources will be served in place of all other files\nFALLBACK:\n";
    // write a version hash
    $cache .= "\n# Hash: " . md5($hash) . "\n";
    // write all to file.
    fwrite($write, $cache);
    fclose($write);
    
    // echo fielname
    echo ($manifest_file);
}

function smarty_cms_help_function_appcache()
{
?>
    <h3>What does this do?</h3>
    <p>Creates a manifest file for HTML5 AppCache</p>
    <h3>How do I use it?</h3>
    <p>Just insert the tag into your template within &lt;html&gt; like: <code>&lt;html manifest='{appcache}'&gt;</code></p>
    <h3>What parameters does it take?</h3>
    <ul>
        <li><em>(optional)</em> <tt>include_folders</tt> - Comma separated folders to scan for.</li>
    </ul>
<?php
}

function smarty_cms_about_function_appcache()
{
?>
    <p>Author: Goran Ilic &lt;ja@ich-mach-das.at&gt;</p>
    <p>Version: 0.1-alpha</p>
    <h3>Change History:</h3>
    <p>Not even finsihed.</p>
    
<?php
}
?>