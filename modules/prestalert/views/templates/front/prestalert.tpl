<!-- Block prestalert -->
{if $prestalert_active}
{if ($prestalert_start lt "Y-m-d H:i:s"|date) && ($prestalert_end gt "Y-m-d H:i:s"|date)}
<div id="prestalert_block" class="block">
    <a href="{$prestalert_url}">
        <img src="/upload/{$prestalert_src}" alt="banner">
        <p>{$prestalert_text}</p>
    </a>
</div>
{/if}
{/if}
<!-- /Block prestalert -->