{namespace name="frontend/share_basket/checkout/cart"}

{block name='frontend_checkout_frosh_share_basket_save'}

    {if $shareBasketState === 'basketsavefailed'}

        {include file="frontend/_includes/messages.tpl" type="error" content="{s name=basketsavefailed}{/s}"}

    {else}

        {if $shareBasketState === 'basketsaved'}

            {include file="frontend/_includes/messages.tpl" type="success" content="{s name=basketsaved}{/s}"}

        {else}

            {include file="frontend/_includes/messages.tpl" type="info" content="{s name=basketshare}{/s}"}

        {/if}

        <input type="text" class="is--small" id="sharebasket" data-clipboard-target="#sharebasket" readonly value="{$shareBasketUrl}">

        <div class="frosh-share-basket--buttons">
            <a class="btn is--small is--center share-clipboard" data-clipboard-target="#sharebasket">
                <i class="icon--clipboard"></i> {s name="copyurl"}{/s}
            </a>
            {if {config namespace=FroshShareBasket name=email}}
                <a class="btn is--small is--center share-mail"
                   href="mailto:%20?subject={s name='sharetitle'}{/s}%20{$sShopname}&body={$shareBasketUrl}">
                    <i class="icon--mail"></i> {s name="email"}{/s}
                </a>
            {/if}
            {if {config namespace=FroshShareBasket name=facebook}}
                <a class="btn is--small is--center share-facebook" target="_blank"
                   href="https://www.facebook.com/sharer/sharer.php?title={s name='sharetitle'}{/s} {$sShopname}&u={$shareBasketUrl}">
                    <i class="icon--facebook"></i> {s name="facebook"}{/s}
                </a>
            {/if}
            {if {config namespace=FroshShareBasket name=whatsapp}}
                <a class="btn is--small is--center share-whatsapp" target="_blank"
                   href="https://api.whatsapp.com/send?text={s name='sharetitle'}{/s} {$sShopname} {$shareBasketUrl}">
                    <i class="icon--share"></i> {s name="whatsapp"}{/s}
                </a>
            {/if}
            {if {config namespace=FroshShareBasket name=webshare}}
                <a class="btn is--small is--center share-webshare"
                      data-share-title="{s name='sharetitle'}{/s} {$sShopname}"
                      data-share-text=""
                      data-share-url="{$shareBasketUrl}">
                    <i class="icon--share"></i> {s name="webshare"}{/s}
                </a>
            {/if}
        </div>

    {/if}

{/block}
