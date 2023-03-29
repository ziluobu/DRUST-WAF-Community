import CryptoJS from 'crypto-js'

export default {
    // 解密  data：要加密解密的数据，AES_KEY：密钥，IV:偏移量
    decrypt(data, AES_KEY, IV) {
        const key = CryptoJS.enc.Utf8.parse(AES_KEY)
        const iv = CryptoJS.enc.Utf8.parse(IV)
        const decrypt = CryptoJS.AES.decrypt(data, key, {
            iv,
            mode: CryptoJS.mode.CBC,
            padding: CryptoJS.pad.Pkcs7
        }).toString(CryptoJS.enc.Utf8)
        return decrypt
    },
    // 加密
    encrypt(data, AES_KEY, IV) {
        const key = CryptoJS.enc.Utf8.parse(AES_KEY)
        const iv = CryptoJS.enc.Utf8.parse(IV)
        const encrypted = CryptoJS.AES.encrypt(data, key, {
            iv,
            mode: CryptoJS.mode.CBC,
            padding: CryptoJS.pad.Pkcs7
        })
        return encrypted.toString()
    }
}
