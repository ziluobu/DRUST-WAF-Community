import Vue from 'vue'
import VueI18n from 'vue-i18n'
import elementLocaleZhCN from 'element-ui/lib/locale/lang/zh-CN'
import elementLocaleZhTW from 'element-ui/lib/locale/lang/zh-TW'
import elementLocaleEn from 'element-ui/lib/locale/lang/en'

import localeZhCN from './packages/zh-CN'
import localeZhTW from './packages/zh-TW'
import localeEn from './packages/en'

import store from '@/store'

Vue.use(VueI18n)

const messages = {
    'zh-CN': {
        ...elementLocaleZhCN,
        ...localeZhCN
    },
    'zh-TW': {
        ...elementLocaleZhTW,
        ...localeZhTW
    },
    'en': {
        ...elementLocaleEn,
        ...localeEn
    }
}

if (!store.state.settings.defaultLang) {
    const lang = navigator.language || navigator.browserLanguage
    Object.keys(messages).map(key => {
        lang.includes(key) && store.commit('settings/setDefaultLang', key)
    })
}

const i18n = new VueI18n({
    silentTranslationWarn: true,
    fallbackLocale: 'zh-CN',
    locale: store.state.settings.defaultLang,
    messages
})

export default i18n
