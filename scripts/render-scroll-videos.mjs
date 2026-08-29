import { spawn } from 'node:child_process'
import fs from 'node:fs/promises'
import path from 'node:path'
import process from 'node:process'
import { chromium } from 'playwright'
import { createRequire } from 'node:module'

const require = createRequire(import.meta.url)
const ffmpegPath = require('ffmpeg-static')

const projectRoot = process.cwd()
const baseUrl = 'http://127.0.0.1:4173'
const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'

const concepts = [
  {
    name: 'corporate-premium',
    path: '/concepts/corporate-premium/index.html',
    waypoints: [
      { selector: '.corporate-hero', align: 'start', holdMs: 1700 },
      { selector: '#oferta', align: 'center' },
      { selector: '#o-nas', align: 'center' },
      { selector: '.diploma-band', align: 'center' },
      { selector: '#kontakt', align: 'center', holdMs: 1300 },
    ],
  },
  {
    name: 'dynamic-local-service',
    path: '/concepts/dynamic-local-service/index.html',
    waypoints: [
      { selector: '.dynamic-hero', align: 'start', holdMs: 1500 },
      { selector: '#uslugi', align: 'center' },
      { selector: '#jak-zamowic', align: 'center' },
      { selector: '.featured-row', align: 'center' },
      { selector: '#kontakt', align: 'center', holdMs: 1200 },
    ],
  },
  {
    name: 'editorial-creative',
    path: '/concepts/editorial-creative/index.html',
    waypoints: [
      { selector: '.editorial-stage', align: 'start', holdMs: 1500 },
      { selector: '#oferta', align: 'center' },
      { selector: '.product-wall', align: 'center' },
      { selector: '#historia', align: 'center' },
      { selector: '.diploma-editorial', align: 'center' },
      { selector: '#kontakt', align: 'center', holdMs: 1200 },
    ],
  },
]

const presets = {
  desktop: {
    viewport: { width: 1440, height: 900 },
    holdStartMs: 1200,
    holdPerSectionMs: 950,
    holdEndMs: 700,
    transitionMs: 850,
  },
  mobile: {
    viewport: { width: 390, height: 844 },
    holdStartMs: 1000,
    holdPerSectionMs: 900,
    holdEndMs: 650,
    transitionMs: 800,
  },
}

function parseArgs() {
  const args = process.argv.slice(2)
  const selectedConcept = args.find((arg) => arg.startsWith('--concept='))?.split('=')[1]
  const selectedDevice = args.find((arg) => arg.startsWith('--device='))?.split('=')[1]
  return { selectedConcept, selectedDevice }
}

async function exists(filePath) {
  try {
    await fs.access(filePath)
    return true
  } catch {
    return false
  }
}

async function isServerReachable() {
  try {
    const response = await fetch(baseUrl, { signal: AbortSignal.timeout(1500) })
    return response.ok
  } catch {
    return false
  }
}

async function waitForServer() {
  for (let attempt = 0; attempt < 30; attempt += 1) {
    if (await isServerReachable()) return
    await new Promise((resolve) => setTimeout(resolve, 500))
  }

  throw new Error(`Server at ${baseUrl} did not start in time.`)
}

async function ensureServer() {
  if (await isServerReachable()) {
    return { process: null, startedHere: false }
  }

  const serverProcess = spawn('python3', ['-m', 'http.server', '4173'], {
    cwd: projectRoot,
    stdio: 'ignore',
  })

  await waitForServer()
  return { process: serverProcess, startedHere: true }
}

function filterTargets(selectedConcept, selectedDevice) {
  const conceptList = selectedConcept
    ? concepts.filter((concept) => concept.name === selectedConcept)
    : concepts

  if (conceptList.length === 0) {
    throw new Error(`Unknown concept: ${selectedConcept}`)
  }

  const deviceNames = selectedDevice
    ? [selectedDevice]
    : Object.keys(presets)

  for (const deviceName of deviceNames) {
    if (!presets[deviceName]) {
      throw new Error(`Unknown device preset: ${deviceName}`)
    }
  }

  return conceptList.flatMap((concept) =>
    deviceNames.map((deviceName) => ({ concept, deviceName, preset: presets[deviceName] })),
  )
}

async function ensureDirectories() {
  await fs.mkdir(path.join(projectRoot, 'renders', 'videos'), { recursive: true })
  await fs.mkdir(path.join(projectRoot, 'renders', 'source-webm'), { recursive: true })
}

function clamp(value, min, max) {
  return Math.min(Math.max(value, min), max)
}

async function collectWaypoints(page, concept) {
  return page.evaluate((waypoints) => {
    const maxScroll = Math.max(0, document.documentElement.scrollHeight - window.innerHeight)
    const minSectionHeight = window.innerHeight * 0.2

    return waypoints
      .map((waypoint) => {
        const element = document.querySelector(waypoint.selector)
        if (!element) return null

        const rect = element.getBoundingClientRect()
        const top = window.scrollY + rect.top
        const elementHeight = rect.height
        if (elementHeight < minSectionHeight) return null

        let targetY = top
        if (waypoint.align === 'center') {
          targetY = top - (window.innerHeight - Math.min(elementHeight, window.innerHeight * 0.82)) / 2
        }

        return {
          selector: waypoint.selector,
          align: waypoint.align,
          holdMs: waypoint.holdMs,
          targetY: Math.min(Math.max(targetY, 0), maxScroll),
        }
      })
      .filter(Boolean)
  }, concept.waypoints)
}

async function scrollThroughWaypoints(page, preset, waypoints) {
  await page.evaluate(
    async ({ waypoints, holdStartMs, holdPerSectionMs, holdEndMs, transitionMs }) => {
      document.documentElement.style.scrollBehavior = 'auto'
      document.body.style.scrollBehavior = 'auto'

      const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms))
      const easeInOutCubic = (t) => (t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2)

      const animateTo = (targetY, duration) =>
        new Promise((resolve) => {
          const startY = window.scrollY
          const delta = targetY - startY
          const startedAt = performance.now()

          const frame = (now) => {
            const elapsed = now - startedAt
            const progress = Math.min(elapsed / duration, 1)
            const eased = easeInOutCubic(progress)
            window.scrollTo(0, startY + delta * eased)

            if (progress < 1) {
              requestAnimationFrame(frame)
              return
            }

            resolve()
          }

          requestAnimationFrame(frame)
        })

      await wait(holdStartMs)

      for (const waypoint of waypoints) {
        await animateTo(waypoint.targetY, transitionMs)
        await wait(waypoint.holdMs ?? holdPerSectionMs)
      }

      await wait(holdEndMs)
    },
    {
      waypoints,
      holdStartMs: preset.holdStartMs,
      holdPerSectionMs: preset.holdPerSectionMs,
      holdEndMs: preset.holdEndMs,
      transitionMs: preset.transitionMs,
    },
  )
}

async function renderVideo(browser, concept, deviceName, preset) {
  const outputBase = `${concept.name}-${deviceName}`
  const webmDir = path.join(projectRoot, 'renders', 'source-webm')
  const mp4Dir = path.join(projectRoot, 'renders', 'videos')
  const webmOutput = path.join(webmDir, `${outputBase}.webm`)
  const mp4Output = path.join(mp4Dir, `${outputBase}.mp4`)

  const context = await browser.newContext({
    viewport: preset.viewport,
    recordVideo: {
      dir: webmDir,
      size: preset.viewport,
    },
  })

  const page = await context.newPage()
  const video = page.video()

  await page.goto(`${baseUrl}${concept.path}`, { waitUntil: 'networkidle' })
  await page.evaluate(() => document.fonts?.ready)
  await page.waitForTimeout(500)

  const waypoints = await collectWaypoints(page, concept)
  if (waypoints.length === 0) {
    throw new Error(`No waypoints found for ${concept.name}`)
  }

  await scrollThroughWaypoints(page, preset, waypoints)

  await page.waitForTimeout(300)
  await context.close()

  const recordedPath = await video.path()
  await fs.rm(webmOutput, { force: true })
  await fs.rename(recordedPath, webmOutput)

  await transcodeToMp4(webmOutput, mp4Output)

  const totalMs =
    preset.holdStartMs +
    preset.holdEndMs +
    waypoints.reduce((sum, waypoint) => sum + (waypoint.holdMs ?? preset.holdPerSectionMs), 0) +
    preset.transitionMs * waypoints.length

  return { webmOutput, mp4Output, totalMs, waypointCount: waypoints.length }
}

async function transcodeToMp4(inputPath, outputPath) {
  await fs.rm(outputPath, { force: true })

  await new Promise((resolve, reject) => {
    const ffmpeg = spawn(ffmpegPath, [
      '-y',
      '-i', inputPath,
      '-c:v', 'libx264',
      '-pix_fmt', 'yuv420p',
      '-movflags', '+faststart',
      outputPath,
    ])

    let stderr = ''
    ffmpeg.stderr.on('data', (chunk) => {
      stderr += chunk.toString()
    })

    ffmpeg.on('exit', (code) => {
      if (code === 0) {
        resolve()
        return
      }

      reject(new Error(`ffmpeg failed for ${path.basename(outputPath)}\n${stderr}`))
    })
  })
}

async function main() {
  if (!(await exists(chromePath))) {
    throw new Error(`Chrome executable not found at ${chromePath}`)
  }

  const { selectedConcept, selectedDevice } = parseArgs()
  const targets = filterTargets(selectedConcept, selectedDevice)
  const server = await ensureServer()
  await ensureDirectories()

  const browser = await chromium.launch({
    executablePath: chromePath,
    headless: true,
  })

  try {
    for (const target of targets) {
      const result = await renderVideo(browser, target.concept, target.deviceName, target.preset)
      console.log(`Rendered ${target.concept.name} / ${target.deviceName} (${Math.round(result.totalMs)}ms, ${result.waypointCount} waypoints)`)
      console.log(`  webm: ${path.relative(projectRoot, result.webmOutput)}`)
      console.log(`  mp4:  ${path.relative(projectRoot, result.mp4Output)}`)
    }
  } finally {
    await browser.close()
    if (server.startedHere && server.process) {
      server.process.kill('SIGTERM')
    }
  }
}

main().catch((error) => {
  console.error(error)
  process.exitCode = 1
})
