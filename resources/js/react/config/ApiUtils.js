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
    var res = await fetch('https://7e03-103-56-183-203.ngrok.io' + '/api' + url, requestOptions);
    var json = await res.json();
    return json;
}