/**
* 2007-2023 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2023 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

function updateLanguageSelector() {
    if (!$('.language-selector .dropdown-menu li').length) {
        return false;
    }
    let iso = false;
    let isos = prestashop.modules.mwrfctest;
    if (typeof prestashop.language.iso !== 'undefined') {
        iso = prestashop.language.iso;
    }
    let url_parts = prestashop.urls.current_url.split("/"),
        current_page = url_parts[url_parts.length - 1],
        current_lang_iso = url_parts[url_parts.length - 2],
        base_url = prestashop.urls.base_url;
    $('.language-selector .dropdown-menu li a').each(function (el) {
        let href = $(this).attr('href'),
            href_parts = href.replace(current_page, '').split("/"),
            iso = href_parts[href_parts.length - 2],
            url = base_url + iso + '/' + prestashop.modules.mwrfctest[iso];
        $(this).attr('href', url);
    });

}

$(document).ready(function () {
    updateLanguageSelector();
})