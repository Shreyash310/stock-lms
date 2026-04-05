-- ============================================
-- StockVerse - Stock Market Learning Platform
-- Database Setup Script
-- ============================================

CREATE DATABASE IF NOT EXISTS stock_lms
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE stock_lms;

-- ============================================
-- TABLES
-- ============================================

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT '📊',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS chapters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content LONGTEXT,
    order_index INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    chapter_id INT NOT NULL,
    status ENUM('in_progress', 'completed') DEFAULT 'in_progress',
    completed_at TIMESTAMP NULL,
    UNIQUE KEY unique_user_chapter (user_id, chapter_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chapter_id INT NOT NULL,
    question TEXT NOT NULL,
    option1 VARCHAR(255) NOT NULL,
    option2 VARCHAR(255) NOT NULL,
    option3 VARCHAR(255) NOT NULL,
    option4 VARCHAR(255) NOT NULL,
    correct_answer TINYINT NOT NULL COMMENT '1-4',
    FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    chapter_id INT NOT NULL,
    score INT NOT NULL,
    total INT NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- SEED DATA
-- ============================================

-- Admin user (password: password)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@stockverse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Demo user (password: password)
INSERT INTO users (name, email, password, role) VALUES
('Demo User', 'demo@stockverse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- ============================================
-- MODULES
-- ============================================

INSERT INTO modules (id, title, description, icon) VALUES
(1, 'Introduction to Stock Markets', 'Learn the fundamentals of stock markets, how they work, and why they matter. Perfect for absolute beginners starting their investment journey.', '📈'),
(2, 'Technical Analysis', 'Master the art of reading stock charts, identifying patterns, and using technical indicators to make informed trading decisions.', '📊'),
(3, 'Fundamental Analysis', 'Understand how to evaluate a company''s financial health, read balance sheets, and determine the intrinsic value of stocks.', '🏦'),
(4, 'Trading Strategies', 'Explore various trading strategies including day trading, swing trading, and position trading. Learn risk management and position sizing.', '🎯'),
(5, 'Mutual Funds & ETFs', 'Discover the world of mutual funds and exchange-traded funds. Learn about NAV, expense ratios, SIPs, and how to build a diversified portfolio.', '💼');

-- ============================================
-- CHAPTERS
-- ============================================

-- Module 1: Introduction to Stock Markets
INSERT INTO chapters (id, module_id, title, content, order_index) VALUES
(1, 1, 'What is a Stock Market?', '
<h2>Understanding the Stock Market</h2>
<p>A <strong>stock market</strong> is a marketplace where buyers and sellers come together to trade shares of publicly listed companies. Think of it as a large, organized auction house where ownership stakes in companies change hands every second.</p>

<h3>Key Concepts</h3>
<ul>
    <li><strong>Stocks (Shares):</strong> A stock represents a small piece of ownership in a company. When you buy a stock, you become a partial owner — a shareholder.</li>
    <li><strong>Stock Exchange:</strong> The physical or electronic platform where stocks are traded. Examples include the NYSE (New York Stock Exchange), NASDAQ, BSE (Bombay Stock Exchange), and NSE (National Stock Exchange).</li>
    <li><strong>Market Participants:</strong> Retail investors, institutional investors, market makers, and brokers all interact in the stock market.</li>
</ul>

<h3>How Does It Work?</h3>
<p>Companies raise capital by issuing shares to the public through an Initial Public Offering (IPO). Once listed, these shares can be bought and sold freely on the stock exchange. The price of a stock is determined by <strong>supply and demand</strong> — if more people want to buy a stock than sell it, the price goes up, and vice versa.</p>

<div class="info-box">
    <h4>💡 Did You Know?</h4>
    <p>The oldest stock exchange in the world is the Amsterdam Stock Exchange, founded in 1602 by the Dutch East India Company. It''s now part of Euronext.</p>
</div>

<h3>Why Do Stock Markets Exist?</h3>
<ol>
    <li><strong>Capital Formation:</strong> Companies can raise money to grow their business</li>
    <li><strong>Wealth Creation:</strong> Investors can grow their wealth through capital appreciation and dividends</li>
    <li><strong>Economic Indicator:</strong> Stock markets reflect the overall health of an economy</li>
    <li><strong>Liquidity:</strong> Investors can easily buy and sell their investments</li>
</ol>
', 1),

(2, 1, 'How Stock Trading Works', '
<h2>The Mechanics of Stock Trading</h2>
<p>Understanding how stock trading works is essential before you place your first trade. Let''s break down the entire process from opening an account to executing a trade.</p>

<h3>Setting Up for Trading</h3>
<ol>
    <li><strong>Demat Account:</strong> A dematerialized account that holds your shares in electronic form (like a bank account for stocks)</li>
    <li><strong>Trading Account:</strong> An account with a stockbroker that allows you to place buy and sell orders</li>
    <li><strong>Bank Account:</strong> Linked to your trading account for fund transfers</li>
</ol>

<h3>Types of Orders</h3>
<table class="content-table">
    <tr><th>Order Type</th><th>Description</th><th>When to Use</th></tr>
    <tr><td>Market Order</td><td>Buy/sell at the current market price</td><td>When you want immediate execution</td></tr>
    <tr><td>Limit Order</td><td>Buy/sell at a specific price or better</td><td>When you want price control</td></tr>
    <tr><td>Stop Loss</td><td>Automatically sell when price drops to a set level</td><td>To limit potential losses</td></tr>
    <tr><td>GTT (Good Till Triggered)</td><td>Order stays active until the trigger price is hit</td><td>For long-term price targets</td></tr>
</table>

<h3>The Trading Day</h3>
<p>Indian stock markets operate from <strong>9:15 AM to 3:30 PM</strong> on weekdays. Here''s the breakdown:</p>
<ul>
    <li><strong>Pre-open Session (9:00 - 9:15):</strong> Orders are collected and the opening price is determined</li>
    <li><strong>Normal Trading (9:15 - 3:30):</strong> Regular buying and selling of stocks</li>
    <li><strong>Post-close Session (3:40 - 4:00):</strong> Closing price is calculated</li>
</ul>

<div class="info-box">
    <h4>⚡ Pro Tip</h4>
    <p>As a beginner, always use <strong>limit orders</strong> instead of market orders. This gives you control over the price at which you buy or sell, preventing unexpected fills during volatile markets.</p>
</div>
', 2),

(3, 1, 'Understanding Market Indices', '
<h2>Stock Market Indices</h2>
<p>A <strong>market index</strong> is a statistical measure that tracks the performance of a group of stocks. It acts as a barometer for the overall market or a specific sector.</p>

<h3>Major Indian Indices</h3>
<ul>
    <li><strong>NIFTY 50:</strong> Tracks the top 50 companies listed on the NSE. It''s the most widely followed index in India and represents about 65% of the total market capitalization.</li>
    <li><strong>SENSEX (BSE 30):</strong> Tracks the top 30 companies listed on the BSE. It''s the oldest index in India, established in 1986.</li>
</ul>

<h3>Major Global Indices</h3>
<ul>
    <li><strong>S&P 500:</strong> Tracks 500 large US companies</li>
    <li><strong>Dow Jones Industrial Average:</strong> Tracks 30 major US companies</li>
    <li><strong>NASDAQ Composite:</strong> Heavy in technology stocks</li>
    <li><strong>FTSE 100:</strong> Top 100 UK companies</li>
    <li><strong>Nikkei 225:</strong> Top 225 Japanese companies</li>
</ul>

<h3>How Are Indices Calculated?</h3>
<p>There are two main methods:</p>
<ol>
    <li><strong>Market Cap Weighted:</strong> Companies with higher market cap have more influence on the index (e.g., NIFTY 50). A 1% move in Reliance has more impact than a 1% move in a smaller company.</li>
    <li><strong>Price Weighted:</strong> Companies with higher stock prices have more influence (e.g., Dow Jones).</li>
</ol>

<h3>Why Do Indices Matter?</h3>
<p>Indices serve several purposes:</p>
<ul>
    <li><strong>Benchmarking:</strong> Compare your portfolio''s performance against the index</li>
    <li><strong>Market Sentiment:</strong> A rising index indicates bullish sentiment; a falling one indicates bearish sentiment</li>
    <li><strong>Index Funds:</strong> Many funds simply replicate an index, making it easy to invest in the entire market</li>
</ul>
', 3);

-- Module 2: Technical Analysis
INSERT INTO chapters (id, module_id, title, content, order_index) VALUES
(4, 2, 'Introduction to Charts', '
<h2>Reading Stock Charts</h2>
<p>Technical analysis is all about analyzing price charts to predict future price movements. A stock chart visually represents the price history of a security over time.</p>

<h3>Types of Charts</h3>
<ol>
    <li>
        <strong>Line Chart:</strong>
        <p>The simplest chart type. It connects closing prices with a line. Best for identifying long-term trends at a glance.</p>
    </li>
    <li>
        <strong>Bar Chart (OHLC):</strong>
        <p>Shows Open, High, Low, and Close prices for each time period. Each bar provides four data points, giving more information than a line chart.</p>
    </li>
    <li>
        <strong>Candlestick Chart:</strong>
        <p>The most popular chart type among traders. Similar to bar charts but with a colored body. A <span style="color: #22c55e;">green/white candle</span> means the close was higher than the open (bullish). A <span style="color: #ef4444;">red/black candle</span> means the close was lower than the open (bearish).</p>
    </li>
</ol>

<h3>Candlestick Anatomy</h3>
<ul>
    <li><strong>Body:</strong> The thick part showing the range between open and close</li>
    <li><strong>Upper Shadow (Wick):</strong> The thin line above the body showing the high</li>
    <li><strong>Lower Shadow (Tail):</strong> The thin line below the body showing the low</li>
</ul>

<h3>Timeframes</h3>
<p>Charts can be viewed in different timeframes:</p>
<ul>
    <li><strong>Intraday:</strong> 1-min, 5-min, 15-min, 1-hour (for day traders)</li>
    <li><strong>Daily:</strong> Each candle = 1 day (most common for swing traders)</li>
    <li><strong>Weekly/Monthly:</strong> For long-term investors</li>
</ul>

<div class="info-box">
    <h4>🎯 Key Takeaway</h4>
    <p>Start with <strong>daily candlestick charts</strong> as a beginner. They provide the right balance of information and are the standard for most trading analysis.</p>
</div>
', 1),

(5, 2, 'Support and Resistance', '
<h2>Support & Resistance Levels</h2>
<p>Support and resistance are foundational concepts in technical analysis. They represent price levels where buying or selling pressure tends to be strong enough to prevent the price from moving further.</p>

<h3>Support</h3>
<p><strong>Support</strong> is a price level where a stock tends to stop falling and bounces back up. Think of it as a floor — buyers step in at this level because they consider the stock "cheap."</p>

<h3>Resistance</h3>
<p><strong>Resistance</strong> is a price level where a stock tends to stop rising and pulls back down. Think of it as a ceiling — sellers step in at this level because they consider the stock "expensive."</p>

<h3>Key Principles</h3>
<ul>
    <li><strong>Role Reversal:</strong> When a support level is broken, it often becomes a new resistance level, and vice versa. This is one of the most powerful concepts in technical analysis.</li>
    <li><strong>Strength:</strong> The more times a level is tested, the stronger it becomes. A support level that has held 5 times is more significant than one that held once.</li>
    <li><strong>Volume:</strong> High volume at support/resistance levels adds confirmation to their significance.</li>
</ul>

<h3>How to Identify Support & Resistance</h3>
<ol>
    <li>Look for prices where the stock has repeatedly bounced or reversed</li>
    <li>Use round numbers (₹100, ₹500, ₹1000) — these often act as psychological S/R levels</li>
    <li>Previous highs and lows serve as natural S/R levels</li>
    <li>Moving averages can act as dynamic support/resistance</li>
</ol>

<h3>Trading with S/R Levels</h3>
<ul>
    <li><strong>Buy near support:</strong> Place buy orders near identified support levels</li>
    <li><strong>Sell near resistance:</strong> Take profits near resistance levels</li>
    <li><strong>Stop loss:</strong> Place below support (for long) or above resistance (for short)</li>
    <li><strong>Breakout trading:</strong> Trade in the direction of a confirmed breakout through S/R</li>
</ul>
', 2),

(6, 2, 'Moving Averages', '
<h2>Moving Averages Explained</h2>
<p>A <strong>moving average (MA)</strong> smooths out price data to create a single flowing line, making it easier to identify the direction of the trend. It''s one of the most widely used technical indicators.</p>

<h3>Types of Moving Averages</h3>

<h4>1. Simple Moving Average (SMA)</h4>
<p>The SMA calculates the average closing price over a specified number of periods. For example, a 50-day SMA adds up the closing prices of the last 50 days and divides by 50.</p>

<h4>2. Exponential Moving Average (EMA)</h4>
<p>The EMA gives more weight to recent prices, making it more responsive to new information. It reacts faster to price changes compared to the SMA.</p>

<h3>Common Moving Average Periods</h3>
<table class="content-table">
    <tr><th>Period</th><th>Use Case</th><th>Trader Type</th></tr>
    <tr><td>9 or 21 EMA</td><td>Short-term trend identification</td><td>Day/Swing traders</td></tr>
    <tr><td>50 SMA/EMA</td><td>Medium-term trend</td><td>Swing traders</td></tr>
    <tr><td>100 SMA</td><td>Medium-to-long-term</td><td>Position traders</td></tr>
    <tr><td>200 SMA</td><td>Long-term trend (most important)</td><td>All traders & investors</td></tr>
</table>

<h3>Trading Signals</h3>
<ul>
    <li><strong>Golden Cross:</strong> When the 50-day MA crosses ABOVE the 200-day MA → Bullish signal</li>
    <li><strong>Death Cross:</strong> When the 50-day MA crosses BELOW the 200-day MA → Bearish signal</li>
    <li><strong>Price above MA:</strong> Generally bullish trend</li>
    <li><strong>Price below MA:</strong> Generally bearish trend</li>
</ul>

<div class="info-box">
    <h4>📌 Remember</h4>
    <p>Moving averages are <strong>lagging indicators</strong> — they are based on past prices and will always be behind the current price. Use them for trend confirmation, not prediction. Combine with other indicators for better results.</p>
</div>
', 3);

-- Module 3: Fundamental Analysis
INSERT INTO chapters (id, module_id, title, content, order_index) VALUES
(7, 3, 'Reading Financial Statements', '
<h2>Financial Statements Explained</h2>
<p>Financial statements are the report cards of a company. They tell you everything about a company''s financial health, profitability, and growth potential. Every publicly listed company is required to publish these statements quarterly and annually.</p>

<h3>The Three Key Financial Statements</h3>

<h4>1. Income Statement (Profit & Loss Statement)</h4>
<p>Shows the company''s revenue, expenses, and profit over a period of time.</p>
<ul>
    <li><strong>Revenue (Top Line):</strong> Total money earned from selling products/services</li>
    <li><strong>COGS:</strong> Cost of Goods Sold — direct costs of production</li>
    <li><strong>Gross Profit:</strong> Revenue minus COGS</li>
    <li><strong>Operating Expenses:</strong> Salaries, rent, marketing, R&D</li>
    <li><strong>EBITDA:</strong> Earnings Before Interest, Taxes, Depreciation & Amortization</li>
    <li><strong>Net Profit (Bottom Line):</strong> The final profit after all expenses and taxes</li>
</ul>

<h4>2. Balance Sheet</h4>
<p>A snapshot of what the company owns (assets) and owes (liabilities) at a specific point in time.</p>
<p>The fundamental equation: <strong>Assets = Liabilities + Shareholders'' Equity</strong></p>

<h4>3. Cash Flow Statement</h4>
<p>Tracks the actual cash moving in and out of the company, divided into:</p>
<ul>
    <li><strong>Operating Cash Flow:</strong> Cash from core business operations</li>
    <li><strong>Investing Cash Flow:</strong> Cash spent on/received from investments and assets</li>
    <li><strong>Financing Cash Flow:</strong> Cash from borrowings, equity, and dividends</li>
</ul>

<div class="info-box">
    <h4>🔑 Golden Rule</h4>
    <p>A company can report profits but still run out of cash. That''s why the Cash Flow Statement is considered the most important indicator of true financial health. <strong>Cash is king!</strong></p>
</div>
', 1),

(8, 3, 'Key Financial Ratios', '
<h2>Financial Ratios Every Investor Should Know</h2>
<p>Financial ratios help you compare companies on an apples-to-apples basis, regardless of their size. Think of them as quick diagnostic tools for a company''s health.</p>

<h3>Valuation Ratios</h3>
<table class="content-table">
    <tr><th>Ratio</th><th>Formula</th><th>What It Tells You</th></tr>
    <tr><td>P/E Ratio</td><td>Price / Earnings Per Share</td><td>How much are investors willing to pay for ₹1 of earnings. Lower P/E = potentially undervalued</td></tr>
    <tr><td>P/B Ratio</td><td>Price / Book Value Per Share</td><td>How much premium investors pay over the net asset value. P/B < 1 might mean undervalued</td></tr>
    <tr><td>PEG Ratio</td><td>P/E / Earnings Growth Rate</td><td>P/E adjusted for growth. PEG < 1 = potentially undervalued</td></tr>
    <tr><td>EV/EBITDA</td><td>Enterprise Value / EBITDA</td><td>Valuation metric independent of capital structure. Useful for comparing companies with different debt levels</td></tr>
</table>

<h3>Profitability Ratios</h3>
<ul>
    <li><strong>ROE (Return on Equity):</strong> Net Profit / Shareholders'' Equity — measures how efficiently the company uses investor money. &gt;15% is generally considered good.</li>
    <li><strong>ROA (Return on Assets):</strong> Net Profit / Total Assets — how well the company uses its assets to generate profit.</li>
    <li><strong>Net Profit Margin:</strong> Net Profit / Revenue — what percentage of revenue translates to profit.</li>
    <li><strong>Operating Margin:</strong> Operating Profit / Revenue — profitability from core operations.</li>
</ul>

<h3>Debt Ratios</h3>
<ul>
    <li><strong>Debt-to-Equity (D/E):</strong> Total Debt / Shareholders'' Equity. High D/E means the company relies heavily on borrowed money. D/E < 1 is generally preferred.</li>
    <li><strong>Interest Coverage Ratio:</strong> EBIT / Interest Expense. Higher is better — shows the company can comfortably pay its interest. Below 1.5 is risky.</li>
    <li><strong>Current Ratio:</strong> Current Assets / Current Liabilities. Measures short-term liquidity. Above 1.5 is comfortable.</li>
</ul>

<div class="info-box">
    <h4>⚠️ Important</h4>
    <p>Never rely on a single ratio. Always look at multiple ratios together and compare with industry peers. A low P/E doesn''t always mean a stock is cheap — it might be low for a reason!</p>
</div>
', 2),

(9, 3, 'Evaluating a Company', '
<h2>How to Evaluate a Company for Investment</h2>
<p>Putting it all together — here''s a systematic framework for evaluating whether a company is worth investing in.</p>

<h3>Step 1: Understand the Business</h3>
<ul>
    <li>What does the company do? Can you explain it in one sentence?</li>
    <li>What industry/sector does it operate in?</li>
    <li>What is its competitive advantage (moat)?</li>
    <li>Who are its competitors?</li>
</ul>

<h3>Step 2: Check Financial Health</h3>
<ol>
    <li>Is revenue growing consistently? (Check 5-year trend)</li>
    <li>Is net profit growing? Is the profit margin stable or improving?</li>
    <li>Is the debt level manageable? (D/E ratio < 1 preferred)</li>
    <li>Is the company generating positive free cash flow?</li>
    <li>Is ROE consistently above 15%?</li>
</ol>

<h3>Step 3: Valuation Check</h3>
<ul>
    <li>Compare P/E ratio with industry average</li>
    <li>Check PEG ratio — is growth justified compared to the valuation?</li>
    <li>Is the current price below intrinsic value estimates?</li>
</ul>

<h3>Step 4: Management Quality</h3>
<ul>
    <li>Track record of the management team</li>
    <li>Corporate governance practices</li>
    <li>Insider buying or selling activity</li>
    <li>Are they shareholder-friendly? (Dividends, buybacks)</li>
</ul>

<h3>Step 5: Risk Assessment</h3>
<ul>
    <li>Regulatory risks</li>
    <li>Concentration risk (too dependent on one product/client)</li>
    <li>Cyclical vs. defensive nature</li>
    <li>Global/macro-economic factors</li>
</ul>

<div class="info-box">
    <h4>📋 The Warren Buffett Checklist</h4>
    <p>"Invest in businesses you understand, with durable competitive advantages, run by honest and competent management, available at a fair price." — Warren Buffett</p>
</div>
', 3);

-- Module 4: Trading Strategies (chapters)
INSERT INTO chapters (id, module_id, title, content, order_index) VALUES
(10, 4, 'Day Trading Basics', '
<h2>Introduction to Day Trading</h2>
<p>Day trading involves buying and selling financial instruments within the same trading day. All positions are closed before the market closes, so there''s no overnight risk.</p>

<h3>Key Characteristics</h3>
<ul>
    <li><strong>Timeframe:</strong> Minutes to hours (never overnight)</li>
    <li><strong>Capital Requirement:</strong> Higher margin requirements for intraday trading</li>
    <li><strong>Volume:</strong> Multiple trades per day</li>
    <li><strong>Focus:</strong> Small price movements for quick profits</li>
</ul>

<h3>Essential Skills for Day Traders</h3>
<ol>
    <li><strong>Quick Decision Making:</strong> Markets move fast, and hesitation can be costly</li>
    <li><strong>Technical Analysis:</strong> Chart reading and pattern recognition</li>
    <li><strong>Risk Management:</strong> Strict stop-losses and position sizing</li>
    <li><strong>Emotional Discipline:</strong> Sticking to your trading plan</li>
    <li><strong>Market Awareness:</strong> News, events, and sector movements</li>
</ol>

<h3>Common Day Trading Strategies</h3>
<ul>
    <li><strong>Scalping:</strong> Making dozens of trades for very small profits (₹0.50 - ₹2 per share)</li>
    <li><strong>Momentum Trading:</strong> Trading stocks that are moving significantly in one direction with high volume</li>
    <li><strong>Breakout Trading:</strong> Entering when price breaks above resistance or below support</li>
    <li><strong>Reversal Trading:</strong> Identifying when a trend is about to reverse</li>
</ul>

<div class="info-box">
    <h4>⚠️ Reality Check</h4>
    <p>Studies show that over 90% of day traders lose money. Day trading is NOT a get-rich-quick scheme. Start with paper trading (virtual money) before risking real capital. Build your skills over months, not days.</p>
</div>
', 1),

(11, 4, 'Risk Management', '
<h2>Risk Management: The Key to Long-Term Survival</h2>
<p>Risk management is the single most important skill a trader can develop. Without it, even the best strategy will eventually lead to ruin. As the saying goes: <em>"Take care of the downside, and the upside will take care of itself."</em></p>

<h3>The 1% Rule</h3>
<p>Never risk more than <strong>1-2% of your total trading capital</strong> on a single trade. This means if you have ₹10,00,000 in your trading account, the maximum loss on any single trade should be ₹10,000 - ₹20,000.</p>

<h3>Position Sizing Formula</h3>
<p><strong>Number of Shares = Risk Amount / (Entry Price - Stop Loss Price)</strong></p>
<p>Example: Capital = ₹5,00,000, Risk = 1% (₹5,000), Entry = ₹500, Stop Loss = ₹485</p>
<p>Shares = ₹5,000 / (₹500 - ₹485) = ₹5,000 / ₹15 = 333 shares</p>

<h3>Stop Loss Strategies</h3>
<ul>
    <li><strong>Fixed Stop Loss:</strong> Set a specific price level below your entry (e.g., 2% below)</li>
    <li><strong>Trailing Stop Loss:</strong> Moves up with the price, locking in profits as the trade moves in your favor</li>
    <li><strong>Volatility-Based Stop:</strong> Use ATR (Average True Range) to set stops based on the stock''s natural price movement</li>
    <li><strong>Support-Based Stop:</strong> Place stop loss just below a key support level</li>
</ul>

<h3>Risk-Reward Ratio</h3>
<p>Always aim for a <strong>minimum 1:2 risk-reward ratio</strong>. This means for every ₹1 you risk, you should aim to make at least ₹2. With this ratio, you can be wrong 50% of the time and still be profitable.</p>

<div class="info-box">
    <h4>💎 The Golden Rules of Risk Management</h4>
    <ul>
        <li>Never trade without a stop loss</li>
        <li>Never move your stop loss further away</li>
        <li>Never risk money you can''t afford to lose</li>
        <li>Diversify across sectors and stocks</li>
        <li>Keep a trading journal and review your trades</li>
    </ul>
</div>
', 2),

(12, 4, 'Building a Trading Plan', '
<h2>Your Personal Trading Plan</h2>
<p>A trading plan is your personal rule book that defines exactly how, when, and what you will trade. With no plan, you''re gambling. With a plan, you''re running a business.</p>

<h3>Components of a Trading Plan</h3>

<h4>1. Trading Goals</h4>
<ul>
    <li>Monthly/yearly return targets (realistic: 2-5% monthly)</li>
    <li>Maximum acceptable drawdown</li>
    <li>Learning goals (new strategies, skills)</li>
</ul>

<h4>2. Market Selection</h4>
<ul>
    <li>Which markets will you trade? (Equities, F&O, commodities)</li>
    <li>Which sectors/stocks do you focus on?</li>
    <li>What market conditions are best for your strategy?</li>
</ul>

<h4>3. Entry Rules</h4>
<ul>
    <li>What specific conditions must be met before entering?</li>
    <li>What technical indicators do you use for confirmation?</li>
    <li>What timeframe do you analyze?</li>
</ul>

<h4>4. Exit Rules</h4>
<ul>
    <li>Where is your stop loss placed?</li>
    <li>Where are your profit targets?</li>
    <li>Do you scale out of positions?</li>
    <li>Under what conditions do you exit early?</li>
</ul>

<h4>5. Risk Management Rules</h4>
<ul>
    <li>Maximum risk per trade (1-2%)</li>
    <li>Maximum number of open positions</li>
    <li>Maximum daily loss limit (stop trading for the day)</li>
    <li>Position sizing method</li>
</ul>

<div class="info-box">
    <h4>📝 Action Step</h4>
    <p>Write your trading plan down before placing any real trade. Review and update it monthly based on your performance and lessons learned. The plan is a living document — it evolves as you grow as a trader.</p>
</div>
', 3);

-- Module 5: Mutual Funds & ETFs
INSERT INTO chapters (id, module_id, title, content, order_index) VALUES
(13, 5, 'Understanding Mutual Funds', '
<h2>Mutual Funds 101</h2>
<p>A <strong>mutual fund</strong> is a professionally managed investment vehicle that pools money from many investors to buy a diversified portfolio of stocks, bonds, or other securities.</p>

<h3>How Mutual Funds Work</h3>
<ol>
    <li>Investors buy units of the mutual fund</li>
    <li>A professional fund manager invests the pooled money</li>
    <li>Returns (gains/losses) are distributed proportionally among investors</li>
    <li>The fund charges a fee (expense ratio) for management</li>
</ol>

<h3>Types of Mutual Funds</h3>
<ul>
    <li><strong>Equity Funds:</strong> Invest primarily in stocks (higher risk, higher potential returns)</li>
    <li><strong>Debt Funds:</strong> Invest in bonds and fixed-income securities (lower risk, steady returns)</li>
    <li><strong>Hybrid Funds:</strong> Mix of equity and debt (balanced approach)</li>
    <li><strong>Index Funds:</strong> Replicate a market index like NIFTY 50 (passive, low-cost)</li>
    <li><strong>ELSS:</strong> Equity Linked Savings Scheme — offers tax benefits under Section 80C</li>
</ul>

<h3>Key Terms</h3>
<ul>
    <li><strong>NAV (Net Asset Value):</strong> The per-unit price of the fund, calculated daily</li>
    <li><strong>Expense Ratio:</strong> Annual fee charged by the fund (lower is better). Direct plans have lower expense ratios than regular plans.</li>
    <li><strong>SIP (Systematic Investment Plan):</strong> Invest a fixed amount regularly (monthly). This averages out your purchase price through rupee cost averaging.</li>
    <li><strong>Exit Load:</strong> Fee charged if you withdraw before a specified period</li>
</ul>

<div class="info-box">
    <h4>💰 SIP Magic</h4>
    <p>A monthly SIP of just ₹5,000 in a fund that returns 12% annually would grow to approximately <strong>₹50 lakhs in 20 years</strong> and <strong>₹1.76 crores in 30 years</strong>. The power of compounding is real!</p>
</div>
', 1),

(14, 5, 'Exchange Traded Funds (ETFs)', '
<h2>ETFs: The Best of Both Worlds</h2>
<p>An <strong>ETF (Exchange Traded Fund)</strong> combines the diversification benefits of a mutual fund with the trading flexibility of a stock. ETFs are traded on the stock exchange just like regular stocks.</p>

<h3>ETFs vs Mutual Funds</h3>
<table class="content-table">
    <tr><th>Feature</th><th>ETF</th><th>Mutual Fund</th></tr>
    <tr><td>Trading</td><td>Real-time on exchange</td><td>End of day NAV</td></tr>
    <tr><td>Expense Ratio</td><td>Very low (0.01% - 0.5%)</td><td>Higher (0.5% - 2.5%)</td></tr>
    <tr><td>Minimum Investment</td><td>1 unit (price of 1 share)</td><td>₹500 - ₹5,000</td></tr>
    <tr><td>SIP Available</td><td>Not typically</td><td>Yes</td></tr>
    <tr><td>Liquidity</td><td>Intraday</td><td>T+1 or T+2 days</td></tr>
    <tr><td>Management</td><td>Mostly passive</td><td>Active or passive</td></tr>
</table>

<h3>Popular ETFs in India</h3>
<ul>
    <li><strong>Nifty 50 ETFs:</strong> Nifty BeES, ICICI Nifty ETF, SBI Nifty ETF</li>
    <li><strong>Gold ETFs:</strong> Gold BeES, SBI Gold ETF (invest in gold without physical storage)</li>
    <li><strong>Bank ETFs:</strong> Bank BeES (tracks NIFTY Bank index)</li>
    <li><strong>International ETFs:</strong> Motilal Oswal NASDAQ 100 ETF, Mirae S&P 500 ETF</li>
</ul>

<h3>When to Choose ETFs</h3>
<ul>
    <li>You want the lowest possible expense ratio</li>
    <li>You want to trade during market hours</li>
    <li>You already have a Demat + trading account</li>
    <li>You prefer passive index investing</li>
</ul>

<div class="info-box">
    <h4>🌟 Pro Strategy</h4>
    <p>For long-term wealth building, consider a <strong>core-satellite approach</strong>: Put 70-80% of your portfolio in broad market ETFs/index funds (core), and 20-30% in individual stocks or sector bets (satellite).</p>
</div>
', 2);

-- ============================================
-- QUIZZES
-- ============================================

-- Quiz for Chapter 1: What is a Stock Market?
INSERT INTO quizzes (chapter_id, question, option1, option2, option3, option4, correct_answer) VALUES
(1, 'What does a stock represent?', 'A loan to a company', 'A partial ownership in a company', 'A government bond', 'A fixed deposit', 2),
(1, 'What determines the price of a stock?', 'The company''s CEO', 'The government', 'Supply and demand', 'The stock exchange', 3);

-- Quiz for Chapter 2: How Stock Trading Works
INSERT INTO quizzes (chapter_id, question, option1, option2, option3, option4, correct_answer) VALUES
(2, 'Which type of order gives you control over the execution price?', 'Market Order', 'Limit Order', 'Random Order', 'Flash Order', 2),
(2, 'What are the normal trading hours for Indian stock markets?', '10:00 AM - 4:00 PM', '9:15 AM - 3:30 PM', '8:00 AM - 5:00 PM', '9:00 AM - 4:00 PM', 2);

-- Quiz for Chapter 3: Understanding Market Indices
INSERT INTO quizzes (chapter_id, question, option1, option2, option3, option4, correct_answer) VALUES
(3, 'How many companies does the NIFTY 50 index track?', '30', '100', '50', '500', 3),
(3, 'What type of weighting does the NIFTY 50 use?', 'Price weighted', 'Equal weighted', 'Market cap weighted', 'Revenue weighted', 3);

-- Quiz for Chapter 4: Introduction to Charts
INSERT INTO quizzes (chapter_id, question, option1, option2, option3, option4, correct_answer) VALUES
(4, 'Which chart type is most popular among traders?', 'Line chart', 'Bar chart', 'Point & Figure chart', 'Candlestick chart', 4),
(4, 'What does a green/white candlestick indicate?', 'The close was lower than the open', 'The close was higher than the open', 'The stock is expensive', 'The stock has low volume', 2);

-- Quiz for Chapter 5: Support and Resistance
INSERT INTO quizzes (chapter_id, question, option1, option2, option3, option4, correct_answer) VALUES
(5, 'What is support in technical analysis?', 'A price level where selling pressure is strong', 'A price level where buying pressure prevents further decline', 'The highest price of a stock', 'A technical indicator', 2),
(5, 'What happens when a support level is broken?', 'It disappears', 'It often becomes a new resistance level', 'The stock always recovers', 'Trading is halted', 2);

-- Quiz for Chapter 6: Moving Averages
INSERT INTO quizzes (chapter_id, question, option1, option2, option3, option4, correct_answer) VALUES
(6, 'What is a Golden Cross?', '50-day MA crosses below 200-day MA', '50-day MA crosses above 200-day MA', 'Stock price hits an all-time high', 'RSI crosses above 70', 2),
(6, 'Moving averages are classified as what type of indicator?', 'Leading indicator', 'Lagging indicator', 'Coincident indicator', 'Neutral indicator', 2);

-- Quiz for Chapter 7: Reading Financial Statements
INSERT INTO quizzes (chapter_id, question, option1, option2, option3, option4, correct_answer) VALUES
(7, 'What is the fundamental accounting equation?', 'Revenue = Expenses + Profit', 'Assets = Liabilities + Shareholders'' Equity', 'Cash = Revenue - Expenses', 'Profit = Assets - Liabilities', 2),
(7, 'Which financial statement is considered the best indicator of true financial health?', 'Income Statement', 'Balance Sheet', 'Cash Flow Statement', 'Annual Report', 3);

-- Quiz for Chapter 8: Key Financial Ratios
INSERT INTO quizzes (chapter_id, question, option1, option2, option3, option4, correct_answer) VALUES
(8, 'What does a P/E ratio of 20 mean?', 'The stock price is ₹20', 'Investors pay ₹20 for every ₹1 of earnings', 'The company has 20% profit margin', 'The stock has 20% returns', 2),
(8, 'A Debt-to-Equity ratio below 1 generally indicates what?', 'The company is highly leveraged', 'The company has more equity than debt', 'The company is unprofitable', 'The stock is overvalued', 2);

-- Quiz for Chapter 10: Day Trading Basics
INSERT INTO quizzes (chapter_id, question, option1, option2, option3, option4, correct_answer) VALUES
(10, 'What percentage of day traders typically lose money?', '50%', '70%', '90%+', '30%', 3),
(10, 'What does scalping involve?', 'Holding positions for weeks', 'Making many trades for very small profits', 'Only trading once a day', 'Only buying stocks', 2);

-- Quiz for Chapter 11: Risk Management
INSERT INTO quizzes (chapter_id, question, option1, option2, option3, option4, correct_answer) VALUES
(11, 'According to the 1% rule, how much should you risk per trade?', '1-2% of total trading capital', '10% of total capital', '1% of the stock price', '50% of daily profit', 1),
(11, 'What is the minimum recommended risk-reward ratio?', '1:1', '1:2', '3:1', '1:0.5', 2);

-- Quiz for Chapter 13: Understanding Mutual Funds
INSERT INTO quizzes (chapter_id, question, option1, option2, option3, option4, correct_answer) VALUES
(13, 'What is NAV?', 'Net Annual Value', 'New Asset Venture', 'Net Asset Value', 'National Average Value', 3),
(13, 'What is a SIP?', 'Stock Investment Portfolio', 'Systematic Investment Plan', 'Simple Interest Payment', 'Share Index Plan', 2);

-- Quiz for Chapter 14: Exchange Traded Funds
INSERT INTO quizzes (chapter_id, question, option1, option2, option3, option4, correct_answer) VALUES
(14, 'How are ETFs different from mutual funds in terms of trading?', 'ETFs can only be traded at month end', 'ETFs trade in real-time on exchanges like stocks', 'ETFs cannot be traded', 'There is no difference', 2),
(14, 'What is the core-satellite portfolio strategy?', 'Only invest in core sectors', 'Put 70-80% in index funds and 20-30% in active bets', 'Invest only in satellite communication stocks', 'Split equally among all sectors', 2);
