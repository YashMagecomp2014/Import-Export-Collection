import { appconfig } from "./config";

export const GlobalAPIcall = async (method, url, data = {}, headers = {}) => {
    var requestOptions = {
        method: method,
        headers: {
            ...headers,
            'url': appconfig.shopurl,
        }
    };
    if(method != "GET" && method != "HEAD") {
        requestOptions.body = data;
    }
    console.log(requestOptions, "requestOptions");
    var res = await fetch('https://collectionie.magecomp.us' + '/api' + url, requestOptions);
    var json = await res.json();
    return json;
}