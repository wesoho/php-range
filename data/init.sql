-- ============================================================
-- PHP-Range 靶场数据库初始化（SQLite）
-- ============================================================

-- 靶场登录用户（密码故意弱哈希/明文，演示认证漏洞用）
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY,
    username TEXT UNIQUE,
    password TEXT,
    role TEXT DEFAULT 'user'
);
INSERT OR IGNORE INTO users (id,username,password,role) VALUES (1,'admin','admin123','admin');
INSERT OR IGNORE INTO users (id,username,password,role) VALUES (2,'guest','guest123','user');
INSERT OR IGNORE INTO users (id,username,password,role) VALUES (3,'test','test123','user');
INSERT OR IGNORE INTO users (id,username,password,role) VALUES (4,'pikachu','pikachu','user');

-- 通关进度
CREATE TABLE IF NOT EXISTS progress (
    user TEXT, module TEXT, level_no INTEGER, passed INTEGER, ts TEXT,
    PRIMARY KEY(user,module,level_no)
);

-- 攻击尝试记录（复盘用）
CREATE TABLE IF NOT EXISTS attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user TEXT, module TEXT, level_no INTEGER, passed INTEGER, payload TEXT, ts TEXT
);

-- 阶段测验成绩
CREATE TABLE IF NOT EXISTS quiz_scores (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user TEXT, category TEXT, score INTEGER, total INTEGER, ts TEXT
);

-- ===== SQL 注入靶场数据 =====
-- 用户表（字符型/登录注入）
CREATE TABLE IF NOT EXISTS sqli_users (
    id INTEGER PRIMARY KEY,
    username TEXT,
    password TEXT,
    email TEXT
);
INSERT OR IGNORE INTO sqli_users (id,username,password,email) VALUES (1,'admin','flag{sqli_l1_admin}','admin@range.local');
INSERT OR IGNORE INTO sqli_users (id,username,password,email) VALUES (2,'guest','guest_pwd','guest@range.local');
INSERT OR IGNORE INTO sqli_users (id,username,password,email) VALUES (3,'test','test_pwd','test@range.local');
INSERT OR IGNORE INTO sqli_users (id,username,password,email) VALUES (4,'secret','flag{sqli_blind_secret}','secret@range.local');
INSERT OR IGNORE INTO sqli_users (id,username,password,email) VALUES (5,'flagholder','flag{sqli_l25_waf}','flag@range.local');

-- 商品表（数字型注入）
CREATE TABLE IF NOT EXISTS goods (
    id INTEGER PRIMARY KEY,
    name TEXT,
    price REAL,
    descr TEXT
);
INSERT OR IGNORE INTO goods (id,name,price,descr) VALUES (1,'键盘',199.00,'机械键盘');
INSERT OR IGNORE INTO goods (id,name,price,descr) VALUES (2,'鼠标',89.00,'无线鼠标');
INSERT OR IGNORE INTO goods (id,name,price,descr) VALUES (3,'显示器',1299.00,'4K显示器');
INSERT OR IGNORE INTO goods (id,name,price,descr) VALUES (4,'耳机',399.00,'降噪耳机');
INSERT OR IGNORE INTO goods (id,name,price,descr) VALUES (5,'主机',5999.00,'游戏主机');

-- 留言板（存储型XSS、二次注入、搜索型注入）
CREATE TABLE IF NOT EXISTS messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user TEXT,
    content TEXT,
    ts TEXT DEFAULT (datetime('now'))
);

-- 二次注入：用户资料表（存入时不触发，读取时触发）
CREATE TABLE IF NOT EXISTS profiles (
    id INTEGER PRIMARY KEY,
    username TEXT,
    nickname TEXT
);
INSERT OR IGNORE INTO profiles (id,username,nickname) VALUES (1,'admin','管理员');
INSERT OR IGNORE INTO profiles (id,username,nickname) VALUES (2,'guest','访客');
