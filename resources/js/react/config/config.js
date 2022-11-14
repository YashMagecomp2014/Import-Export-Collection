const queryString = window.location.search;
const urlParams = new URLSearchParams(queryString);
const shop = urlParams.get('shop')

export const appconfig = {
    shopurl: shop
}