{extends file=$layout}
{block name='content'}
    <h1>{if isset($mwrfctest.title) && ($mwrfctest.title)}{$mwrfctest.title}{/if}</h1>
    <p>{if isset($mwrfctest.description) && ($mwrfctest.description)}{$mwrfctest.description}{/if}</p>
{/block}