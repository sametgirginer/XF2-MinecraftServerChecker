# Minecraft Server Checker for Threads - XenForo 2
It simply pulls server data with Custom Thread Field.

## Features

- Server data is updated with CRON every 5 minutes.
    - CRON: `*/5 * * * *`
    - API: [mcsrvstat.us](https://mcsrvstat.us/)
- Easy setup & detailed informations.
- Supports regex for custom thread field.
    - REGEX: `/(([a-zA-Z0-9-])+[.]+([a-zA-Z0-9-])+[.]+([a-zA-Z0-9-])+)|([a-zA-Z0-9-])+[.]+([a-zA-Z0-9-])+/g`

## Installing XenForo add-on

1. Download latest release.
2. **Upload .zip** to your XenForo forum using the "**Install/upgrade from archive**" button in **Admin-Add-ons** (admin.php?add-ons).
3. **Select applicable forums** for the **custom thread field** (admin.php?custom-thread-fields/msc_server_ip/edit).
4. Go to a applicable thread and fill in the "**Minecraft Server IP Address**" field.

### CSS Examples (for msc.less)
------------
```less
.msc-status {
	color: #efefef;
}

.msc-online {
    background: #249a24;
}

.msc-offline {
    background: #9a2424;
}

.msc-copyip {
	color: #185886;
	border-color: #e5e5e5;
}
```