// src/js/dbQueue.js

const DB_NAME = "prodriver-queue";
const STORE_NAME = "requests";
const DB_VERSION = 1;

function openDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(DB_NAME, DB_VERSION);

    request.onupgradeneeded = (event) => {
      const db = event.target.result;
      if (!db.objectStoreNames.contains(STORE_NAME)) {
        db.createObjectStore(STORE_NAME, { keyPath: "id", autoIncrement: true });
      }
    };

    request.onsuccess = (event) => resolve(event.target.result);
    request.onerror = (event) => reject(event.target.error);
  });
};

export async function queueRequest(data) {
  const db = await openDB();

  let cleanData = { ...data };

  // 🧩 Normalize FormData or stringified JSON to plain object
  if (data?.options?.body instanceof FormData) {
    const obj = {};
    for (const [key, value] of data.options.body.entries()) {
      obj[key] = value;
    }
    cleanData.options.body = obj;
  } 
  
  if (typeof data?.options?.body === 'string') {
    try {
      cleanData.options.body = JSON.parse(data.options.body);
    } catch {
      // if it's already string, wrap it in an object
      cleanData.options.body = { raw: data.options.body };
    }
  }

  // 🧩 Always ensure Content-Type is application/json
  if (!cleanData.options.headers) cleanData.options.headers = {};
  cleanData.options.headers['Content-Type'] = 'application/json';

  return new Promise((resolve, reject) => {
    const tx = db.transaction(STORE_NAME, "readwrite");
    tx.objectStore(STORE_NAME).add({ ...cleanData, timestamp: Date.now() });
    tx.oncomplete = () => {
      db.close();
      resolve();
    };
    tx.onerror = (e) => reject(e);
  });
};

export async function getAllQueued() {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const tx = db.transaction(STORE_NAME, "readonly");
    const store = tx.objectStore(STORE_NAME);
    const req = store.getAll();
    req.onsuccess = () => {
      db.close();
      resolve(req.result);
    };
    req.onerror = (e) => reject(e);
  });
};

export async function clearQueued(id) {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const tx = db.transaction(STORE_NAME, "readwrite");
    tx.objectStore(STORE_NAME).delete(id);
    tx.oncomplete = () => {
      db.close();
      resolve();
    };
    tx.onerror = (e) => reject(e);
  });
};
