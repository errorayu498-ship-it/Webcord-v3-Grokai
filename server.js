// ╔═══════════════════════════════════════════════════════════════════════════════╗
// ║              WEBCORD PORTAL - NODE.JS SERVER                                  ║
// ║              Professional Web Server with Error Handling                       ║
// ╚═══════════════════════════════════════════════════════════════════════════════╝

const http = require('http');
const fs = require('fs');
const path = require('path');

// ═══════════════════════════════════════════════════════════════════════════════
// CONFIGURATION
// ═══════════════════════════════════════════════════════════════════════════════

const PORT = process.env.PORT || 3000;
const NODE_ENV = process.env.NODE_ENV || 'development';
const HOSTNAME = '0.0.0.0'; // Railway requires this

// Color codes for console output
const colors = {
    reset: '\x1b[0m',
    bright: '\x1b[1m',
    green: '\x1b[32m',
    yellow: '\x1b[33m',
    red: '\x1b[31m',
    blue: '\x1b[34m',
    cyan: '\x1b[36m'
};

// ═══════════════════════════════════════════════════════════════════════════════
// LOGGING UTILITIES
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Log information to console with timestamp
 */
function logInfo(message) {
    const timestamp = new Date().toISOString();
    console.log(`${colors.cyan}[${timestamp}]${colors.reset} ${colors.green}ℹ${colors.reset} ${message}`);
}

/**
 * Log warning to console
 */
function logWarning(message) {
    const timestamp = new Date().toISOString();
    console.log(`${colors.cyan}[${timestamp}]${colors.reset} ${colors.yellow}⚠${colors.reset} ${message}`);
}

/**
 * Log error to console
 */
function logError(message, error = null) {
    const timestamp = new Date().toISOString();
    console.error(`${colors.cyan}[${timestamp}]${colors.reset} ${colors.red}✕${colors.reset} ${message}`);
    if (error) {
        console.error(`${colors.red}   Error: ${error.message}${colors.reset}`);
    }
}

/**
 * Log success to console
 */
function logSuccess(message) {
    const timestamp = new Date().toISOString();
    console.log(`${colors.cyan}[${timestamp}]${colors.reset} ${colors.green}✓${colors.reset} ${message}`);
}

// ═══════════════════════════════════════════════════════════════════════════════
// FILE UTILITIES
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Safe file reading with error handling
 */
function readFileSync(filePath) {
    try {
        return fs.readFileSync(filePath, 'utf-8');
    } catch (error) {
        logError(`Failed to read file: ${filePath}`, error);
        throw error;
    }
}

/**
 * Get MIME type based on file extension
 */
function getMimeType(fileName) {
    const mimeTypes = {
        '.html': 'text/html; charset=utf-8',
        '.js': 'application/javascript',
        '.css': 'text/css',
        '.json': 'application/json',
        '.png': 'image/png',
        '.jpg': 'image/jpeg',
        '.jpeg': 'image/jpeg',
        '.gif': 'image/gif',
        '.svg': 'image/svg+xml',
        '.ico': 'image/x-icon',
        '.woff': 'font/woff',
        '.woff2': 'font/woff2',
        '.ttf': 'font/ttf',
        '.eot': 'application/vnd.ms-fontobject'
    };

    const ext = path.extname(fileName).toLowerCase();
    return mimeTypes[ext] || 'text/plain';
}

// ═══════════════════════════════════════════════════════════════════════════════
// REQUEST UTILITIES
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Send response with proper headers
 */
function sendResponse(res, statusCode, contentType, data) {
    res.writeHead(statusCode, {
        'Content-Type': contentType,
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Methods': 'GET, POST, OPTIONS, PUT, DELETE',
        'Access-Control-Allow-Headers': 'Content-Type, Authorization',
        'Cache-Control': 'no-cache, no-store, must-revalidate',
        'X-Frame-Options': 'SAMEORIGIN',
        'X-Content-Type-Options': 'nosniff'
    });
    res.end(data);
}

/**
 * Send HTML response
 */
function sendHtml(res, html, statusCode = 200) {
    sendResponse(res, statusCode, 'text/html; charset=utf-8', html);
}

/**
 * Send JSON response
 */
function sendJson(res, data, statusCode = 200) {
    sendResponse(res, statusCode, 'application/json', JSON.stringify(data, null, 2));
}

/**
 * Send error response
 */
function sendError(res, statusCode, message) {
    logWarning(`Client error: ${statusCode} - ${message}`);
    sendJson(res, {
        error: true,
        status: statusCode,
        message: message,
        timestamp: new Date().toISOString()
    }, statusCode);
}

/**
 * Parse URL and extract path and query
 */
function parseUrl(urlString) {
    try {
        const url = new URL(urlString, `http://localhost:${PORT}`);
        return {
            pathname: url.pathname,
            query: Object.fromEntries(url.searchParams),
            hash: url.hash
        };
    } catch (error) {
        logError('URL parsing error:', error);
        return { pathname: '/', query: {}, hash: '' };
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// REQUEST HANDLERS
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Handle preflight OPTIONS requests (CORS)
 */
function handleOptions(res) {
    res.writeHead(200, {
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Methods': 'GET, POST, OPTIONS, PUT, DELETE',
        'Access-Control-Allow-Headers': 'Content-Type, Authorization',
        'Content-Length': 0
    });
    res.end();
}

/**
 * Handle root path - serve index.html
 */
function handleRoot(res) {
    try {
        const indexPath = path.join(__dirname, 'index.html');
        
        // Check if file exists
        if (!fs.existsSync(indexPath)) {
            logError(`Index file not found at ${indexPath}`);
            return sendError(res, 404, 'Portal file (index.html) not found. Make sure to rename webcord-portal.html to index.html');
        }

        const html = readFileSync(indexPath);
        sendHtml(res, html, 200);
        logInfo('Served index.html to client');
    } catch (error) {
        logError('Error serving root path:', error);
        sendError(res, 500, 'Internal Server Error: Failed to serve portal');
    }
}

/**
 * Handle health check endpoint
 */
function handleHealthCheck(res) {
    sendJson(res, {
        status: 'operational',
        service: 'WEBCORD Portal',
        uptime: process.uptime(),
        timestamp: new Date().toISOString(),
        environment: NODE_ENV
    }, 200);
    logInfo('Health check request successful');
}

/**
 * Handle API endpoints
 */
function handleApi(req, res, pathname) {
    switch (pathname) {
        case '/api/health':
        case '/api/health/':
            return handleHealthCheck(res);
        
        case '/api/status':
        case '/api/status/':
            return sendJson(res, {
                status: 'active',
                message: 'WEBCORD Portal is running',
                version: '1.0.0',
                features: {
                    webhook: true,
                    botToken: true,
                    multiToken: true
                }
            });

        case '/api/info':
        case '/api/info/':
            return sendJson(res, {
                name: 'WEBCORD Portal',
                version: '1.0.0',
                author: 'Subhan',
                features: [
                    'Webhook Message Sender',
                    'Bot/User Token Sender',
                    'Multi-token Support',
                    'Real-time Progress Tracking',
                    'Rate Limit Detection',
                    'Error Handling'
                ]
            });

        default:
            return sendError(res, 404, 'API endpoint not found');
    }
}

/**
 * Handle static file serving (if in public folder)
 */
function handleStatic(res, pathname) {
    try {
        const publicPath = path.join(__dirname, 'public', pathname);
        
        // Security: prevent directory traversal
        if (!publicPath.startsWith(path.join(__dirname, 'public'))) {
            return sendError(res, 403, 'Access forbidden');
        }

        // Check if file exists
        if (!fs.existsSync(publicPath)) {
            return sendError(res, 404, 'File not found');
        }

        const data = readFileSync(publicPath);
        const mimeType = getMimeType(pathname);
        sendResponse(res, 200, mimeType, data);
        logInfo(`Served static file: ${pathname}`);
    } catch (error) {
        logError(`Error serving static file: ${pathname}`, error);
        sendError(res, 500, 'Internal Server Error');
    }
}

/**
 * Handle 404 - Not Found
 */
function handle404(res, pathname) {
    const notFoundHtml = `
    <!DOCTYPE html>
    <html>
    <head>
        <title>404 - Not Found</title>
        <style>
            body { 
                background: #1a1a1a; 
                color: #FFD700; 
                font-family: Arial; 
                text-align: center; 
                padding: 50px;
            }
            h1 { font-size: 3em; margin-bottom: 20px; }
            a { color: #FFD700; text-decoration: none; }
            a:hover { text-decoration: underline; }
        </style>
    </head>
    <body>
        <h1>404 - Not Found</h1>
        <p>The path <code>${pathname}</code> does not exist</p>
        <p><a href="/">← Back to Portal</a></p>
    </body>
    </html>
    `;
    sendHtml(res, notFoundHtml, 404);
}

// ═══════════════════════════════════════════════════════════════════════════════
// MAIN REQUEST HANDLER
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Main HTTP request handler
 */
function requestHandler(req, res) {
    try {
        const method = req.method.toUpperCase();
        const url = parseUrl(req.url);
        const pathname = url.pathname;

        // Log incoming request
        logInfo(`${method} ${pathname}`);

        // Handle CORS preflight
        if (method === 'OPTIONS') {
            return handleOptions(res);
        }

        // Only allow GET requests for portal
        if (method !== 'GET') {
            return sendError(res, 405, 'Method not allowed');
        }

        // Route handling
        if (pathname === '/' || pathname === '' || pathname === '/index.html') {
            return handleRoot(res);
        }

        if (pathname.startsWith('/api/')) {
            return handleApi(req, res, pathname);
        }

        if (pathname.startsWith('/public/')) {
            return handleStatic(res, pathname.slice(7));
        }

        // Default to portal (for routes like /webhook, /bot)
        if (pathname !== '/') {
            logWarning(`Unhandled route requested: ${pathname}, serving portal`);
            return handleRoot(res);
        }

        // 404
        return handle404(res, pathname);

    } catch (error) {
        logError('Request handler error:', error);
        sendError(res, 500, 'Internal Server Error');
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// SERVER INITIALIZATION
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Create and start HTTP server
 */
function startServer() {
    try {
        const server = http.createServer(requestHandler);

        // Handle server errors
        server.on('error', (error) => {
            logError('Server error:', error);
            if (error.code === 'EADDRINUSE') {
                logError(`Port ${PORT} is already in use. Try a different port.`);
                process.exit(1);
            }
        });

        // Server started
        server.on('listening', () => {
            logSuccess(`═══════════════════════════════════════════════════════════════`);
            logSuccess(`🚀 WEBCORD Portal is running!`);
            logSuccess(`═══════════════════════════════════════════════════════════════`);
            logInfo(`🌐 Local:   http://localhost:${PORT}`);
            logInfo(`🌐 Network: http://${getLocalIp()}:${PORT}`);
            logInfo(`📝 Environment: ${NODE_ENV}`);
            logInfo(`📊 API Health: http://localhost:${PORT}/api/health`);
            logSuccess(`═══════════════════════════════════════════════════════════════`);
            logInfo('Portal ready to accept connections!');
        });

        // Handle graceful shutdown
        process.on('SIGTERM', () => {
            logWarning('SIGTERM received, shutting down gracefully...');
            server.close(() => {
                logSuccess('Server closed successfully');
                process.exit(0);
            });
        });

        process.on('SIGINT', () => {
            logWarning('SIGINT received, shutting down gracefully...');
            server.close(() => {
                logSuccess('Server closed successfully');
                process.exit(0);
            });
        });

        // Start listening
        server.listen(PORT, HOSTNAME);

    } catch (error) {
        logError('Failed to start server:', error);
        process.exit(1);
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// UTILITY FUNCTIONS
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Get local IP address
 */
function getLocalIp() {
    try {
        const os = require('os');
        const interfaces = os.networkInterfaces();
        for (const name of Object.keys(interfaces)) {
            for (const iface of interfaces[name]) {
                if (iface.family === 'IPv4' && !iface.internal) {
                    return iface.address;
                }
            }
        }
        return 'localhost';
    } catch (error) {
        return 'localhost';
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// STARTUP
// ═══════════════════════════════════════════════════════════════════════════════

// Show startup banner
console.log(`
${colors.yellow}╔═══════════════════════════════════════════════════════════════╗${colors.reset}
${colors.yellow}║          WEBCORD PORTAL - Starting Server...                   ║${colors.reset}
${colors.yellow}║          Professional Message Sender Platform                  ║${colors.reset}
${colors.yellow}╚═══════════════════════════════════════════════════════════════╝${colors.reset}
`);

// Start the server
startServer();

// Handle uncaught exceptions
process.on('uncaughtException', (error) => {
    logError('Uncaught Exception:', error);
    process.exit(1);
});

process.on('unhandledRejection', (reason, promise) => {
    logError(`Unhandled Rejection at ${promise}:`, reason);
});

module.exports = { startServer, requestHandler };
