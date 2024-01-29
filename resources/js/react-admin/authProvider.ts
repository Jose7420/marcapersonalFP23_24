// in resources/js/react_admin/authProvider.js

import { dataProvider } from "./dataProvider";

export const authProvider = {
    login: ({ username, password }) => {
        return dataProvider.createToken( username, password )
        .then(response => {
            if (response.status < 200 || response.status >= 300) {
              throw new Error(response.statusText);
            }
            localStorage.setItem('auth', JSON.stringify(response.json));
        })
        .catch((e) => {
                throw new Error('Network error')
        });
    },
    logout: () => {
        let token = localStorage.getItem('auth')
        if (token) {
            token = JSON.parse(localStorage.getItem('auth'))
            localStorage.removeItem('auth');
            return dataProvider.deleteToken
                .then(() => ('login'))
                .catch((error) => {
                    throw error
                });
        } else {
            return Promise.resolve()
        }
    },
    checkAuth: () =>
        (localStorage.getItem('auth') ? Promise.resolve() : Promise.reject()),
    checkError: (error) => {
        const status = error.status;
        if (status === 401 || status === 403) {
            localStorage.removeItem('auth');
            return Promise.reject();
        }
        // other error code (404, 500, etc): no need to log out
        return Promise.resolve();
    },
    getIdentity: () => {
        const token = localStorage.getItem('auth') ? JSON.parse(localStorage.getItem('auth')) : undefined
        if (!token) {
            throw new Error('No auth token');
        }

        return dataProvider.getIdentity()
            .then(( data ) => {
                return data.json
            })
            .catch(() => {
                throw new Error('Network error')
            });
    },
    getPermissions: () => Promise.resolve('')
};
