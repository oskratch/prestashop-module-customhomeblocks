{**
 * Custom Home Blocks — front-office template
 *
 * Variables:
 *   {$blocks} — array of blocks, each with .html (raw HTML) and .title
 **}

{foreach from=$blocks item=block}
<div class="custom-home-block">
    {$block.html nofilter}
</div>
{/foreach}