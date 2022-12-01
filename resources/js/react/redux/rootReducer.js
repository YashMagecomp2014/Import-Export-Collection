const appUtilsState = {
    redirectHistory: false,
    loadHistory: false,
};

export const rootReducer = (state = appUtilsState, action) => {
    switch (action.type) {
        case 'SET_REDIRECT_INDEX':
            return {
                ...state,
                redirectHistory: action.payload,
            };
        case 'SET_LOAD_HISTORY':
            return {
                ...state,
                loadHistory: action.payload,
            };

        default:
            return state;
    }
}
export const setRedirectIndex = (index) => {
    console.log(index,'idnex');
    return {
        type: 'SET_REDIRECT_INDEX',
        payload: index
    };
}

export const enableLoadHistory = () => {
    return {
        type: 'SET_LOAD_HISTORY',
        payload: true
    };
}

export const disableLoadHistory = () => {
    return {
        type: 'SET_LOAD_HISTORY',
        payload: false
    };
}