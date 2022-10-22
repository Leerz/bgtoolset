# bgtoolset

This is a dump of excellent Ps3xploit Team [bgtoolset](https://www.ps3xploit.net/bgtoolset/). **I'm not an author of any of these tools**, all credits go to
Ps3xploit team. If you find these tools useful, please consider a donation via Paypal at team@ps3xploit.net or in BTC at either of the addresses below:

| &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;![Legacy P2PKH](assets/images/qr-legacy-P2PKH.png)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;![Segwit BECH32](assets/images/qr-native-segwit-BECH32.png)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;![ERC20](assets/images/qr-eth-erc20.png)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;![ERC20](assets/images/qr-usdt-erc20.png)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; |
|:---:|:---:|:---:|:---:|
| Legacy P2PKH | Segwit BECH32 | ERC20 | ERC20 |

<p align="center">
<b>Supported versions: 4.80 - 4.89</b>
</p>

## IMPORTANT INFO, please read carefuly.

- **This is NOT official repository of bgtoolset**. It's a dump which I made for myself, to be able jailbreak my console if for whatever reason [Ps3Xploit](https://www.ps3xploit.me/)
website goes down.
- **Use this mirror as a last resort** - you should always go to [bgtoolset page](https://www.ps3xploit.me/) first, and use tools provided there.
  Not only they are more reliable, and written by people who actually know what they're doing - they're also always up to date.
- **I'm not responsible for any damage you may do to your console**. This stuff if used inproperly, can brick your PS3.
- Some people tested this dump on their consoles, and it worked without any issues. However I do not guarantee that it will work for you. See [Tested consoles](#tested-consoles).
- I'm not providing any info how to use this locally, if you don't know it - you probably shouldn't do this.
- It's not a full dump, I only focused on happy path, of flashing PS3. Memory editor probably wouldn't work, logs are also not reliable. Original toolset uses `*.php` files,
  which (for obvious reasons) I couldn't dump, so most of them are just plain HTML output of the scripts. The `file.php` is my dummy, minimal implementation which makes all this stuff work.

## Tested consoles

Bear in mind, with every new firmware release, whole tool changes and needs to be redumped from ps3xploit again. Because of that,
column `Commit` points to the commit (a point in time) which was most recent, when these tests are performed.

Double check this, since "works on this model" actually means "worked back then".

| Variant | Model      | Firmware | Status | Commit                                                                                        | Source                                                                                                   |
|---------|------------|----------|:------:|:---------------------------------------------------------------------------------------------:|----------------------------------------------------------------------------------------------------------|
| SLIM    | CECH-2503B | 4.88     | ✅     | [68f4c41](https://github.com/ajgon/bgtoolset/commit/68f4c41c68178e4e33ac2ef36650468e5111dc7d) | E-mail info                                                                                              |
| SLIM    | CECH-2504  | 4.88     | ✅     | [68f4c41](https://github.com/ajgon/bgtoolset/commit/68f4c41c68178e4e33ac2ef36650468e5111dc7d) | [Twitter](https://twitter.com/leerz25/status/1555749812988809216#m)                                      |
| FAT     | CECH-J03   | 4.87     | ✅     | [68f4c41](https://github.com/ajgon/bgtoolset/commit/68f4c41c68178e4e33ac2ef36650468e5111dc7d) | ajgon                                                                                                    |
| FAT     | CECH-L04   | 4.86     | ✅     | [68f4c41](https://github.com/ajgon/bgtoolset/commit/68f4c41c68178e4e33ac2ef36650468e5111dc7d) | [GitHub Issue](https://github.com/ajgon/bgtoolset/issues/7)                                              |
| SLIM    | CECH-2004A | 4.86     | ✅     | [68f4c41](https://github.com/ajgon/bgtoolset/commit/68f4c41c68178e4e33ac2ef36650468e5111dc7d) | E-mail info                                                                                              |
| SLIM    | CECH-2004B | 4.86     | ✅     | [68f4c41](https://github.com/ajgon/bgtoolset/commit/68f4c41c68178e4e33ac2ef36650468e5111dc7d) | [GitHub Issue](https://github.com/ajgon/bgtoolset/issues/7)                                              |

## Contributors

- [@dracinn](https://github.com/dracinn)
