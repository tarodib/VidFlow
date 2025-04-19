// --- 🔄 yt-dlp Auto-Updater ---
const fs = require('fs');
const https = require('https');
const { spawn, execFile } = require('child_process');

const YT_DLP_PATH = './yt-dlp_linux';
const YT_DLP_DOWNLOAD_URL = 'https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp';
const GITHUB_API_URL = 'https://api.github.com/repos/yt-dlp/yt-dlp/releases/latest';

function getLatestVersion(callback) {
    const options = {
        headers: { 'User-Agent': 'Node.js yt-dlp checker' }
    };

    https.get(GITHUB_API_URL, options, (res) => {
        let data = '';
        res.on('data', chunk => data += chunk);
        res.on('end', () => {
            const json = JSON.parse(data);
            const version = json.tag_name.replace(/^v/, '');
            callback(version);
        });
    }).on('error', err => {
        console.error('❌ Error fetching latest version:', err.message);
    });
}

function getLocalVersion(callback) {
    execFile(YT_DLP_PATH, ['--version'], (err, stdout) => {
        if (err) {
            console.warn('⚠️ yt-dlp not found locally or not executable.');
            return callback(null);
        }
        callback(stdout.trim());
    });
}

function downloadYtDlp(callback) {
    console.log('⬇️ Downloading latest yt-dlp...');

    function followRedirect(url) {
        https.get(url, (res) => {
            if (res.statusCode === 302 || res.statusCode === 301) {
                return followRedirect(res.headers.location);
            }

            const file = fs.createWriteStream(YT_DLP_PATH, { mode: 0o755 });
            res.pipe(file);
            file.on('finish', () => {
                file.close(() => {
                    console.log('✅ yt-dlp downloaded and ready!');
                    callback();
                });
            });
        }).on('error', (err) => {
            console.error('❌ Download error:', err.message);
        });
    }

    followRedirect(YT_DLP_DOWNLOAD_URL);
}

function updateYtDlpIfNeeded(callback) {
    getLatestVersion((latestVersion) => {
        getLocalVersion((localVersion) => {
            if (localVersion === latestVersion) {
                console.log(`👌 yt-dlp is up to date (v${localVersion})`);
                return callback();
            }

            console.log(`🔁 Updating yt-dlp: local=${localVersion || 'none'}, latest=${latestVersion}`);
            downloadYtDlp(callback);
        });
    });
}

updateYtDlpIfNeeded(() => {
const express = require('express');
const { spawn } = require('child_process');

const app = express();
const PORT = 5000;

app.get('/stream', (req, res) => {
    const videoUrl = req.query.url;
    if (!videoUrl) {
        return res.status(400).json({ error: 'Missing video URL' });
    }

    console.log(`Starting stream for: ${videoUrl}`);


    const ytDlp = spawn('./yt-dlp_linux', ['-g', videoUrl]);

    let urls = [];

    ytDlp.stdout.on('data', (data) => {
        urls = data.toString().trim().split('\n');
    });

    ytDlp.stderr.on('data', (error) => {
        console.error(`yt-dlp Error: ${error.toString()}`);
    });

    ytDlp.on('exit', (code) => {
        if (code !== 0 || urls.length < 2) {
            console.error(`yt-dlp failed with code ${code}`);
            return res.status(500).json({ error: 'Failed to retrieve video/audio URLs.' });
        }

        const videoStreamUrl = urls[0];
        const audioStreamUrl = urls[1];

        console.log(`Video URL: ${videoStreamUrl}`);
        console.log(`Audio URL: ${audioStreamUrl}`);

        res.writeHead(200, {
            'Content-Type': 'video/mp4',
        });

const ffmpeg = spawn('ffmpeg', [
            '-user_agent', 'Mozilla/5.0 (iPad; CPU OS 17_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/128.0.6613.92 Mobile/15E148 Safari/604.1',
            '-i', videoStreamUrl,
            '-i', audioStreamUrl,
            '-c:v', 'copy',
            '-c:a', 'copy',
            '-f', 'mp4',
            '-movflags', 'frag_keyframe+empty_moov+faststart',
            'pipe:1',
        ]);

        ffmpeg.stdout.pipe(res);

        ffmpeg.stderr.on('data', (error) => {
            console.error(`FFmpeg Error: ${error.toString()}`);
        });

        ffmpeg.on('exit', (code) => {
            console.log(`FFmpeg exited with code ${code}`);
        });

        req.on('close', () => {
            console.log('Client disconnected. Stopping processes...');
            ffmpeg.kill('SIGINT');
            ytDlp.kill('SIGINT');
        });
    });
});

app.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});

});
