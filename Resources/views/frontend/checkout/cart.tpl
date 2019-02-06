{namespace name="frontend/share_basket/checkout/cart"}
{extends file="parent:frontend/checkout/cart.tpl"}

{block name='frontend_checkout_actions_checkout'}
    <div class="frosh-share-basket--wrapper left">
        {if $shareBasketState == 'basketnotfound'}

            {include file="frontend/_includes/messages.tpl" type="warning" content="{s name="basketnotfound"}{/s}"}

        {elseif $shareBasketState == 'basketloaded'}

            {include file="frontend/_includes/messages.tpl" type="success" content="{s name="basketloaded"}{/s}"}

        {elseif $shareBasketState == 'basketexists'}

            {include file="frontend/frosh_share_basket/save.tpl" shareBasketUrl=$shareBasketUrl}

        {else}

            <div class="frosh-share-basket--response"></div>
            <form action="{url controller=FroshShareBasket action=save}" method="post" class="frosh-share-basket--form">
                <button class="btn is--primary" type="submit" name="Submit" value="submit">
                    <i class="icon--basket"></i> {s name="savebasket"}{/s}
                </button>
            </form>

        {/if}
    </div>
    {$smarty.block.parent}
{/block}

{block name='frontend_basket_basket_is_empty'}
    {if $shareBasketState == 'basketnotfound'}
        <div class="basket--info-messages">
            {include file="frontend/_includes/messages.tpl" type="warning" content="{s name="basketnotfound"}{/s}"}
        </div>
    {/if}
    {$smarty.block.parent}
{/block}
