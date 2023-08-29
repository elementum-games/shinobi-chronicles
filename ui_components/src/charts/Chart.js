/*
//Required on the same page
<script src="
https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js
"></script>
*/

const RadarNinjaChart = (
  {
    playerStats
  }
  ) => {
  React.useEffect(() => {

    const canvasElement = document.getElementById('chart');
    const context = canvasElement.getContext('2d');

    const normalizeValue = (value, minValue, maxValue) => {
      return (value - minValue) / (maxValue - minValue) * 5;
    };

    const bloodline = playerStats.bloodlineSkill;
    const castSpeed = playerStats.castSpeed;
    const genjutsu = playerStats.genjutsuSkill;
    const intelligence = playerStats.intelligence;
    const ninjutsu = playerStats.ninjutsuSkill;
    const speed = playerStats.speed;
    const taijutsu = playerStats.taijutsuSkill;
    const willpower = playerStats.willpower;

    const playerData = [genjutsu, willpower, taijutsu, speed, castSpeed, ninjutsu, intelligence];

    const skillValues = Object.values(playerData);
    const minValue = Math.min(...skillValues);
    const maxValue = Math.max(...skillValues);

    const normalizedStats = {}; //skill value holder

    //for each item in playerData -> normalize[skill] = 0...5;
    for (const [skill, value] of Object.entries(playerData)) {
      normalizedStats[skill] = Math.round(normalizeValue(value, minValue, maxValue));
    }

    //object -> array
    const normalizedStatsArray = Object.values(normalizedStats);

const myChart = new Chart(context, {
      type: 'radar',

      data: {
        labels: ['Genjutsu', 'Willpower', 'Taijutsu', 'Speed', 'Cast Speed', 'Ninjutsu', 'Intelligence'],
        datasets: [{
          data: normalizedStatsArray,
          backgroundColor: 'rgba(180, 200, 230, 0.4)',
          borderColor: 'rgba(180, 200, 230, 0.9)',
          borderWidth: 1
        }]
      },

      options: {
        plugins: {
          legend: {
            display: false
          }
        },
        tooltips: {
          enabled: false
        },
        scales: {
          r: {
            angleLines: {
              color: 'rgba(255, 255, 255, 0.2)'
            },
            grid: {
              color: 'rgba(255, 255, 255, 0.15)'
            },
            pointLabels: {
              color: '#c7b070'
            },
            ticks: {
              display: false
            }
          }
        }
      }
    });

  }, []); // Empty dependency array means this effect runs once after initial render



  return (
  <>
  <canvas id="chart"></canvas>
  </>
  );
};

export default RadarNinjaChart;