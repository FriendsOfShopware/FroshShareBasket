{namespace name="frontend/share_basket/checkout/cart"}
{extends file="parent:frontend/checkout/cart.tpl"}

{block name='frontend_checkout_actions_checkout'}
    <div class="main--actions--sharebasket left">
        {if $froshShareBasket}
            <div class="alert is--success is--rounded">
                <div class="alert--icon">
                    <i class="icon--element icon--check"></i>
                </div>
                <div class="alert--content">{s name="basketsaved"}{/s}</div>
            </div>
            <input type="text" class="is--small" id="sharebasket" data-clipboard-target="#sharebasket" readonly value="{$sBasketUrl}">
            <div class="sharebasket--share--buttons">
                <a class="btn is--small is--center share-mail" data-clipboard-target="#sharebasket">
                    <i class="icon--clipboard"></i> {s name="copyurl"}{/s}
                </a>
                {if {config namespace=FroshShareBasket name=email}}
                    <a class="btn is--small is--center share-mail" href="mailto:%20?subject={s name='sharetitle'}{/s}%20{$sShopname}&body={$sBasketUrl}">
                        <i class="icon--mail"></i> {s name="email"}{/s}
                    </a>
                {/if}
                {if {config namespace=FroshShareBasket name=facebook}}
                    <a class="btn is--small is--center share-facebook" target="_blank" href="https://www.facebook.com/sharer/sharer.php?title={s name='sharetitle'}{/s} {$sShopname}&u={$sBasketUrl}">
                        <i class="icon--facebook"></i> {s name="facebook"}{/s}
                    </a>
                {/if}
                {if {config namespace=FroshShareBasket name=whatsapp}}
                    <a class="btn is--small is--center share-whatsapp" target="_blank" href="https://api.whatsapp.com/send?text={s name='sharetitle'}{/s} {$sShopname} {$sBasketUrl}">
                        <i class="icon--share"></i> {s name="whatsapp"}{/s}
                    </a>
                {/if}
            </div>
        {else}
            <a class="btn is--primary sharebasket--new" href="{url controller=sharebasket action=save}">
                <i class="icon--basket"></i> {s name="savebasket"}{/s}
            </a>
        {/if}
    </div>
    {$smarty.block.parent}
{/block}
