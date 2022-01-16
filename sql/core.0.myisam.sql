drop table if exists  Account
;

drop table if exists  Trade
;


drop table if exists Watch
;


/* ============================================================ */
/*   Table: Account                                             */
/* ============================================================ */
create table Account
(
    AcctId       INT  not null,
    AcctName     VARCHAR(100)           not null,
    AcctNo     VARCHAR(100)           null,
    constraint PK_Account primary key (AcctId)
)
ENGINE=MYISAM;

/* ============================================================ */
/*   Table: Trade                                               */
/* ============================================================ */
create table Trade
(
    TradeId      INT  AUTO_INCREMENT                 not null,
    AcctId INT not null,
    Ticker        VARCHAR(10)           not null,
    DT date not null,
    Side INT not null,
    Type INT not null,
    Qty INT not null,
    EntryPrice float not null,
    TargetPrice float null,
    StopPrice float null,
    EntryDT date null,
    ActualEntryPrice float null,
    ExitDT date null,
    ActualExitPrice float null,
    Status INT not null,
    PL float null,
    PLDollar float null,
    ImageLink varchar(255) null,
    Publish boolean null,
    Idea VARCHAR(4000) null,

    constraint PK_TRADE primary key (TradeId)
)
ENGINE=MYISAM;


/* ============================================================ */
/*   Table: Watch                                               */
/* ============================================================ */
create table Watch
(
    WatchId      INT  AUTO_INCREMENT                 not null,
    AcctId INT not null,
    Ticker        VARCHAR(10)           not null,
    DT date not null,
    DDT date null,
    Status INT not null,
    ImageLink varchar(255) null,
    Publish boolean null,
    Idea VARCHAR(4000) null,

    constraint PK_WATCH primary key (WatchId)
)
ENGINE=MYISAM;


/* FOREIGN KEYS */
alter table Trade
    add constraint FK_TRADE_REF_111_ACCOUNT foreign key  (AcctId)
       references Account (AcctId)
;

alter table Watch
    add constraint FK_Watch_REF_111_ACCOUNT foreign key  (AcctId)
       references Account (AcctId)
;


/* Minimal defaults */
insert into Account(AcctId,AcctName,AcctNo)
values (0,'Default','');

