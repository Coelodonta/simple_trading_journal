# simple_trading_journal
A simple PHP/MySQL trading journal for keeping track of stonks.

# what's this?
As the name suggests, it's a simple personal, multi-account trading journal for keeping track of stock trades and trade ideas. It's intended for people who have outgrown tracking their trades with paper and pencil, and don't want to get stuck in the dreaded Excel rut. I was one of those people, and wrote this app as a quick and dirty alternative for my personal use. It has helped me get a better overview of my trades, so I decided to make the code public it with no warranties of any kind in the hope that somebody might find it useful.

It uses a bare-bones LAMP stack running on a local machine with no external dependencies, frameworks or special libraries, just PHP, HTML and CSS. It also creates standard mysqldump commands to create portable backups.


# a little about the features
- Tracking of stock trades
- Tracking of trade ideas
- Mulitple trading accounts
- Multiple watch lists
- Tracks trades based on status, such as open, in trade, completed, stopped out etc.
- Filter trades and watch lists by date, side, type, status etc.
- Basic trade statistics
- Charts: Shortcuts to Yahoo Finance and Finviz for each trade
- Backup using mysqldump
- Supports market, limit and stop limit trades
- Supports long and short trades
- Automatically calculates profit/loss (% and $) for completed trades if the corresponding fields are set to zero

# installation
- Copy the files to your web server
- Create a database, and a user
- Update app.php with the username etc.
- Run the SQL file core.0.myisam.sql to create the tables and a default account
- Use the command in populate_db.sql as a template to create additional accounts
- Good to go

# a note about security
There is none. This is a single-user application intended to run on a users personal machine as an alternative to text files and Excel spreadsheets. The assumption is that the user is no more likely or motivated to abuse this application than she is to corrupt her own text files or spreadsheets. With that in mind there are no user ids, logins, privileges etc. Everything is public. Also, no particular care has been taken to make it hack-proof. Thus, if somebody wants to run it on a network it would be wise that at a minimum she adds some form of user management, takes a look at the various SQL statements for vulnerabilities, and removes the mysqldump commands.
